<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_can_be_rendered(): void
    {
        // Pengunjung yang belum login akan melihat Landing Page SaaS
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('OrderFlow');
    }

    public function test_authenticated_user_is_redirected_from_landing_to_dashboard(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_register_with_business_fields(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Pemilik Sablon',
            'business_name'         => 'Sablon Berkah',
            'phone'                 => '081234567890',
            'email'                 => 'owner@sablon.test',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'owner@sablon.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('Sablon Berkah', $user->business_name);
        $this->assertSame('081234567890', $user->phone);
    }

    public function test_dashboard_displays_kpi_counters(): void
    {
        $user = User::factory()->create(['business_name' => 'Konveksi Hebat']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pelanggan A',
            'phone'   => '08111111111',
        ]);

        // 1 Active order due today
        Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Kaos Komunitas',
            'quantity'       => 20,
            'price_per_unit' => 50000,
            'total_amount'   => 1000000,
            'deadline'       => now()->toDateString(),
            'status'         => 'production',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Pesanan Aktif');
        $response->assertSee('ORD-0001');
        $response->assertSee('Pelanggan A');
    }

    public function test_can_create_customer(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/customers', [
            'name'    => 'CV Mitra Abadi',
            'phone'   => '081299998888',
            'address' => 'Jl. Industri No. 10',
            'notes'   => 'Langganan sablon seragam',
        ]);

        $response->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', [
            'name'    => 'CV Mitra Abadi',
            'phone'   => '081299998888',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_create_order_with_auto_generated_order_number(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pelanggan Budi',
            'phone'   => '081234567890',
        ]);

        $response = $this->actingAs($user)->post('/orders', [
            'customer_id'    => $customer->id,
            'name'           => '50 Kaos Reuni',
            'quantity'       => 50,
            'price_per_unit' => 25000,
            'total_amount'   => 1250000,
            'dp_amount'      => 500000,
            'dp_method'      => 'transfer',
            'deadline'       => now()->addDays(5)->toDateString(),
            'status'         => 'new',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertSame('ORD-0001', $order->order_number);
        $this->assertSame(1250000.0, (float)$order->total_amount);
        $this->assertSame(500000.0, (float)$order->total_paid);
        $this->assertSame(750000.0, (float)$order->remaining_amount);
        $response->assertRedirect('/orders/' . $order->id);
    }

    public function test_can_update_order_status(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pelanggan',
        ]);

        $order = Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Spanduk',
            'quantity'       => 1,
            'price_per_unit' => 100000,
            'total_amount'   => 1000000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($user)->patch("/orders/{$order->id}/status", [
            'status' => 'production',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('production', $order->fresh()->status);
    }

    public function test_can_record_payment_and_reduce_remaining_balance(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pelanggan Bayar',
        ]);

        $order = Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Buku Menu',
            'quantity'       => 10,
            'price_per_unit' => 100000,
            'total_amount'   => 1000000,
            'status'         => 'production',
        ]);

        $this->actingAs($user)->post("/orders/{$order->id}/payments", [
            'amount'       => 600000,
            'payment_date' => now()->toDateString(),
            'method'       => 'qris',
            'notes'        => 'Cicilan 1',
        ]);

        $this->assertSame(400000.0, (float)$order->fresh()->remaining_amount);
    }

    public function test_whatsapp_service_generates_valid_url(): void
    {
        $user = User::factory()->create(['business_name' => 'Percetakan Berkah']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Ahmad',
            'phone'   => '081234567890',
        ]);

        $order = Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Kartu Nama',
            'quantity'       => 5,
            'price_per_unit' => 30000,
            'total_amount'   => 150000,
            'status'         => 'production',
        ]);

        $this->actingAs($user);
        $service = new WhatsAppService();
        $url = $service->statusUrl($order);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('ORD-0001', urldecode($url));
        $this->assertStringContainsString('Produksi', urldecode($url));
        $this->assertStringContainsString('/lacak/', urldecode($url));
    }

    public function test_public_tracking_page_can_be_viewed_without_login(): void
    {
        $user = User::factory()->create(['business_name' => 'Sablon Juara']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pelanggan Publik',
            'phone'   => '08123456789',
        ]);

        $order = Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => '100 Kaos Acara',
            'quantity'       => 100,
            'price_per_unit' => 50000,
            'total_amount'   => 5000000,
            'status'         => 'production',
        ]);

        $this->assertNotNull($order->tracking_token);

        // Akses tanpa login (guest)
        $response = $this->get('/lacak/' . $order->tracking_token);
        $response->assertOk();
        $response->assertSee('ORD-0001');
        $response->assertSee('100 Kaos Acara');
        $response->assertSee('Sablon Juara');
    }

    public function test_public_tracking_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get('/lacak/invalid-token-123456');
        $response->assertNotFound();
    }

    public function test_order_owner_can_view_invoice(): void
    {
        $user = User::factory()->create(['business_name' => 'Percetakan Hebat']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => 'Pemesan Invoice',
            'phone'   => '08123456789',
        ]);

        $order = Order::create([
            'user_id'        => $user->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Brosur 500 Lembar',
            'quantity'       => 500,
            'price_per_unit' => 2000,
            'total_amount'   => 1000000,
            'status'         => 'completed',
        ]);

        $response = $this->actingAs($user)->get("/orders/{$order->id}/invoice");
        $response->assertOk();
        $response->assertSee('NOTA / INVOICE PESANAN');
        $response->assertSee('Brosur 500 Lembar');
        $response->assertSee('Pemesan Invoice');
    }

    public function test_other_user_cannot_view_invoice(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $userA->id,
            'name'    => 'Customer User A',
        ]);

        $order = Order::create([
            'user_id'        => $userA->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-0001',
            'name'           => 'Item Rahasia',
            'quantity'       => 10,
            'price_per_unit' => 50000,
            'total_amount'   => 500000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($userB)->get("/orders/{$order->id}/invoice");
        $response->assertForbidden();
    }
}
