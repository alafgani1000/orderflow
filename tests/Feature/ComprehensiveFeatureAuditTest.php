<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Payment;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprehensiveFeatureAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. CUSTOMER LIFECYCLE & ISOLATION
     */
    public function test_customer_crud_and_isolation(): void
    {
        $ownerA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $ownerB = User::factory()->create(['role' => User::ROLE_OWNER]);

        // Create customer for Owner A
        $response = $this->actingAs($ownerA)->post(route('customers.store'), [
            'name'    => 'Pelanggan Toko A',
            'phone'   => '081234567890',
            'address' => 'Jl. Merdeka No. 1',
            'notes'   => 'Pelanggan sablon kaos',
        ]);
        $response->assertRedirect(route('customers.index'));
        $customerA = Customer::where('name', 'Pelanggan Toko A')->first();
        $this->assertNotNull($customerA);
        $this->assertSame($ownerA->id, $customerA->user_id);

        // Owner B cannot see or edit Owner A's customer
        $this->actingAs($ownerB)->get(route('customers.show', $customerA))->assertForbidden();
        $this->actingAs($ownerB)->get(route('customers.edit', $customerA))->assertForbidden();
        $this->actingAs($ownerB)->put(route('customers.update', $customerA), [
            'name' => 'Hacked',
        ])->assertForbidden();
        $this->actingAs($ownerB)->delete(route('customers.destroy', $customerA))->assertForbidden();

        // Owner A can update customer
        $this->actingAs($ownerA)->put(route('customers.update', $customerA), [
            'name'    => 'Pelanggan Toko A (VIP)',
            'phone'   => '081234567899',
            'address' => 'Jl. Merdeka No. 1 B',
            'notes'   => 'Diskon 5%',
        ])->assertRedirect(route('customers.show', $customerA));
        $this->assertSame('Pelanggan Toko A (VIP)', $customerA->fresh()->name);

        // Owner A can delete customer
        $this->actingAs($ownerA)->delete(route('customers.destroy', $customerA))->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customerA->id]);
    }

    /**
     * 2. ORDER LIFECYCLE, FILTERS & DELETION POLICY
     */
    public function test_order_crud_filters_and_deletion_policies(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $kasir = User::factory()->create(['role' => User::ROLE_ADMIN_CS, 'owner_id' => $owner->id]);
        $operator = User::factory()->create(['role' => User::ROLE_PRODUCTION, 'owner_id' => $owner->id]);

        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'SMA 1 Juara',
            'phone'   => '081211112222',
        ]);

        // Kasir creates order with size breakdown and DP
        $createResponse = $this->actingAs($kasir)->post(route('orders.store'), [
            'customer_id'    => $customer->id,
            'name'           => 'Kaos Kelas Sablon',
            'quantity'       => 30,
            'price_per_unit' => 60000,
            'total_amount'   => 1800000,
            'status'         => 'new',
            'deadline'       => now()->addDays(3)->toDateString(),
            'dp_amount'      => 800000,
            'dp_method'      => 'transfer',
            'size_breakdown' => [
                'M'  => 10,
                'L'  => 15,
                'XL' => 5,
            ],
        ]);
        $createResponse->assertRedirect();

        $order = Order::where('name', 'Kaos Kelas Sablon')->first();
        $this->assertNotNull($order);
        $this->assertSame($owner->id, $order->user_id);
        $this->assertSame(30, $order->quantity);
        $this->assertEquals(1800000, (int)$order->total_amount);
        $this->assertEquals(800000, (int)$order->total_paid);
        $this->assertEquals(1000000, (int)$order->remaining_amount);
        $this->assertFalse($order->is_paid_off);

        // Operator can view order details and update status
        $this->actingAs($operator)->get(route('orders.show', $order))->assertOk();
        $updateStatusResponse = $this->actingAs($operator)->patchJson(route('orders.update-status', $order), [
            'status' => 'waiting_design',
        ]);
        $updateStatusResponse->assertOk();
        $this->assertSame('waiting_design', $order->fresh()->status);

        // Operator cannot delete order (403)
        $this->actingAs($operator)->delete(route('orders.destroy', $order))->assertForbidden();

        // Kasir cannot delete order (403 - restricted to Owner)
        $this->actingAs($kasir)->delete(route('orders.destroy', $order))->assertForbidden();

        // Owner can delete order
        $deleteResponse = $this->actingAs($owner)->delete(route('orders.destroy', $order));
        $deleteResponse->assertRedirect(route('orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /**
     * 3. ORDER FILE UPLOAD, DOWNLOAD & DELETE
     */
    public function test_order_file_attachment_workflow(): void
    {
        $privateDisk = config('orderflow.storage.private_disk', 'local');
        Storage::fake($privateDisk);
        Storage::fake('public');

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Pelanggan Desain',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-FILE-001',
            'name'           => 'Spanduk Banner',
            'quantity'       => 1,
            'price_per_unit' => 100000,
            'total_amount'   => 100000,
            'status'         => 'waiting_design',
        ]);

        // Upload dummy design mockup file (PDF without GD dependency)
        $file = UploadedFile::fake()->create('mockup_baju.pdf', 500, 'application/pdf');
        $uploadResponse = $this->actingAs($owner)->post(route('orders.files.store', $order), [
            'files' => [$file],
        ]);
        $uploadResponse->assertRedirect();

        $orderFile = OrderFile::where('order_id', $order->id)->first();
        $this->assertNotNull($orderFile);
        $this->assertSame('mockup_baju.pdf', $orderFile->file_name);
        Storage::disk($privateDisk)->assertExists($orderFile->file_path);

        // Download file
        $downloadResponse = $this->actingAs($owner)->get(route('orders.files.download', [$order, $orderFile]));
        $downloadResponse->assertOk();

        // Delete file
        $deleteResponse = $this->actingAs($owner)->delete(route('orders.files.destroy', [$order, $orderFile]));
        $deleteResponse->assertRedirect();
        Storage::disk($privateDisk)->assertMissing($orderFile->file_path);
        $this->assertDatabaseMissing('order_files', ['id' => $orderFile->id]);
    }

    /**
     * 4. SETTINGS & PROFILE MANAGEMENT
     */
    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        // Update profile
        $profileResponse = $this->actingAs($user)->put(route('settings.profile'), [
            'name'          => 'Nama Pemilik Baru',
            'business_name' => 'Sablon Makmur Jaya',
            'phone'         => '089912345678',
            'email'         => 'makmur@sablon.test',
        ]);
        $profileResponse->assertRedirect();
        $this->assertSame('Nama Pemilik Baru', $user->fresh()->name);
        $this->assertSame('Sablon Makmur Jaya', $user->fresh()->business_name);

        // Update password
        $passwordResponse = $this->actingAs($user)->put(route('settings.password'), [
            'current_password'      => 'oldpassword123',
            'password'              => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);
        $passwordResponse->assertRedirect();
        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
    }

    /**
     * 5. WHATSAPP NOTIFICATION LINK BUILDER
     */
    public function test_whatsapp_service_generates_correct_urls_and_messages(): void
    {
        $owner = User::factory()->create(['business_name' => 'Berkah Sablon']);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Pak Hendra',
            'phone'   => '081234567890',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-WA-001',
            'name'           => 'Seragam Futsal 12 Pcs',
            'quantity'       => 12,
            'price_per_unit' => 70000,
            'total_amount'   => 840000,
            'status'         => 'production',
            'size_breakdown' => ['M' => 6, 'L' => 6],
        ]);

        $waService = app(WhatsAppService::class);

        // Run within actingAs($owner) because WhatsAppService reads auth()->user()->business_name
        $this->actingAs($owner);

        // Status Update URL
        $statusUrl = $waService->statusUrl($order);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $statusUrl);
        $this->assertStringContainsString('Produksi', $statusUrl);
        $this->assertStringContainsString(urlencode($order->tracking_url), $statusUrl);

        // Payment Receipt URL
        $paymentUrl = $waService->paymentUrl($order, 400000);
        $decodedPayment = urldecode($paymentUrl);
        $this->assertStringContainsString('pembayaran', $decodedPayment);
        $this->assertStringContainsString('400.000', $decodedPayment);

        // Completed Order URL
        $completedUrl = $waService->completedUrl($order);
        $decodedCompleted = urldecode($completedUrl);
        $this->assertStringContainsString('SELESAI', $decodedCompleted);
        $this->assertStringContainsString('ORD-WA-001', $decodedCompleted);

        // Reminder URL
        $reminderUrl = $waService->reminderUrl($order);
        $decodedReminder = urldecode($reminderUrl);
        $this->assertStringContainsString('sisa tagihan', $decodedReminder);

        // Refund Confirmation URL
        $refundUrl = $waService->refundUrl($order, 100000, 'Pembatalan Sebagian');
        $decodedRefund = urldecode($refundUrl);
        $this->assertStringContainsString('pengembalian dana', $decodedRefund);
        $this->assertStringContainsString('100.000', $decodedRefund);
    }

    /**
     * 6. CUSTOMER AJAX FAST MODAL CREATION
     */
    public function test_customer_ajax_modal_creation_returns_json(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        // Success JSON response
        $response = $this->actingAs($owner)->postJson(route('customers.store'), [
            'name'    => 'PT Sinar Maju Cepat',
            'phone'   => '085712345678',
            'address' => 'Kawasan Industri Cikarang',
            'notes'   => 'Pabrik tekstil',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success'  => true,
            'customer' => [
                'name'  => 'PT Sinar Maju Cepat',
                'phone' => '085712345678',
            ],
        ]);

        $this->assertDatabaseHas('customers', [
            'name'    => 'PT Sinar Maju Cepat',
            'user_id' => $owner->id,
        ]);

        // Validation error returns 422 JSON
        $failResponse = $this->actingAs($owner)->postJson(route('customers.store'), [
            'name' => '', // required
        ]);

        $failResponse->assertStatus(422);
        $failResponse->assertJsonValidationErrors(['name']);
    }

    /**
     * 7. PAYMENT & REFUND ACCOUNTING INTEGRITY
     */
    public function test_refund_system_and_net_balance_calculation(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Budi Karya',
            'phone'   => '081233334444',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-REFUND-01',
            'name'           => 'Spanduk Outdoor 10 Meter',
            'quantity'       => 1,
            'price_per_unit' => 1000000,
            'total_amount'   => 1000000,
            'status'         => 'production',
        ]);

        // 1. Catat pembayaran masuk pertama Rp 600.000
        $this->actingAs($owner)->post(route('orders.payments.store', $order), [
            'type'         => Payment::TYPE_PAYMENT,
            'amount'       => 600000,
            'payment_date' => now()->toDateString(),
            'method'       => 'cash',
            'notes'        => 'DP awal pengerjaan',
        ]);

        $order->refresh();
        $this->assertSame(600000.0, (float)$order->total_paid);
        $this->assertSame(400000.0, (float)$order->remaining_amount);
        $this->assertFalse($order->is_paid);

        // 2. Catat refund keluar Rp 200.000
        $refundResponse = $this->actingAs($owner)->post(route('orders.payments.store', $order), [
            'type'         => Payment::TYPE_REFUND,
            'amount'       => 200000,
            'payment_date' => now()->toDateString(),
            'method'       => 'cash',
            'notes'        => 'Pengembalian karena bahan sisa',
        ]);

        $refundResponse->assertSessionHas('wa_refund_url');

        $order->refresh();
        $this->assertSame(400000.0, (float)$order->total_paid); // 600k - 200k = 400k
        $this->assertSame(200000.0, (float)$order->total_refunded);
        $this->assertTrue($order->has_refund);
        $this->assertSame(600000.0, (float)$order->remaining_amount); // 1000k - 400k = 600k

        // 3. Validasi: Refund tidak boleh melebihi sisa dana yang pernah dibayar (Rp 400.000)
        $invalidRefund = $this->actingAs($owner)->post(route('orders.payments.store', $order), [
            'type'         => Payment::TYPE_REFUND,
            'amount'       => 500000, // melebihi 400k
            'payment_date' => now()->toDateString(),
            'method'       => 'cash',
        ]);

        $invalidRefund->assertSessionHasErrors(['amount']);
        $this->assertSame(400000.0, (float)$order->fresh()->total_paid); // Tidak berubah
    }

    /**
     * 8. QUICK FILTER UNPAID & DASHBOARD FINANCIAL KPIS
     */
    public function test_quick_filter_unpaid_and_dashboard_financials(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Toko Rahmat',
            'phone'   => '081299990000',
        ]);

        // Order Lunas
        $orderPaid = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-LUNAS-01',
            'name'           => 'Stempel Otomatis',
            'quantity'       => 1,
            'price_per_unit' => 100000,
            'total_amount'   => 100000,
            'status'         => 'completed',
        ]);
        Payment::create([
            'order_id'       => $orderPaid->id,
            'type'           => Payment::TYPE_PAYMENT,
            'amount'         => 100000,
            'payment_date'   => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // Order Belum Lunas
        $orderUnpaid = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-BELUMLUNAS-01',
            'name'           => 'Banner 5 Meter',
            'quantity'       => 1,
            'price_per_unit' => 500000,
            'total_amount'   => 500000,
            'status'         => 'production',
        ]);

        // Test filter orders?payment=unpaid
        $responseUnpaid = $this->actingAs($owner)->get(route('orders.index', ['payment' => 'unpaid']));
        $responseUnpaid->assertOk();
        $responseUnpaid->assertSee('ORD-BELUMLUNAS-01');
        $responseUnpaid->assertDontSee('ORD-LUNAS-01');

        // Test dashboard renders financial metrics for owner
        $dashResponse = $this->actingAs($owner)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Ringkasan Keuangan Bulan Ini');
        $dashResponse->assertSee('Kas Masuk Bersih');
        $dashResponse->assertSee('Total Sisa Piutang');
    }
}

