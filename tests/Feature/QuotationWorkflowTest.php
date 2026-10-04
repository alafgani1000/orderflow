<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Quotation;
use App\Models\Subscription;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerWithPlan(?Plan $plan = null): User
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'email_verified_at' => now(),
            'business_name' => 'Sablon Bintang',
            'phone' => '081234567890',
        ]);

        if (! $plan) {
            $plan = Plan::firstOrCreate(
                ['slug' => 'pro'],
                [
                    'name' => 'Pro Juragan',
                    'description' => 'Paket Pro',
                    'price' => 49000,
                    'billing_period' => 'monthly',
                    'max_orders_per_month' => null,
                    'max_employees' => 5,
                    'features' => [],
                    'is_popular' => true,
                    'is_active' => true,
                ]
            );
        }

        Subscription::create([
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        return $owner;
    }

    public function test_owner_can_create_quotation_with_multiple_items(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Budi Santoso', 'phone' => '081299990000']);

        $response = $this->actingAs($owner)->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'title' => 'Penawaran 50 Kaos & 20 Topi Komunitas',
            'valid_until' => now()->addDays(14)->toDateString(),
            'discount' => 50000,
            'tax' => 25000,
            'notes' => 'DP 50% sebelum produksi dimulai.',
            'items' => [
                [
                    'item_name' => 'Kaos Cotton Combed 30s',
                    'description' => 'Sablon DTF A3',
                    'quantity' => 50,
                    'unit_price' => 60000, // 3.000.000
                ],
                [
                    'item_name' => 'Topi Trucker',
                    'description' => 'Bordir Komputer',
                    'quantity' => 20,
                    'unit_price' => 30000, // 600.000
                ],
            ],
        ]);

        $quotation = Quotation::where('user_id', $owner->id)->first();
        $this->assertNotNull($quotation);
        $this->assertSame('Penawaran 50 Kaos & 20 Topi Komunitas', $quotation->title);
        $this->assertStringStartsWith('QUO-', $quotation->quotation_number);
        $this->assertNotNull($quotation->public_token);

        // Subtotal: 3.000.000 + 600.000 = 3.600.000
        $this->assertEquals(3600000, (int) $quotation->subtotal);
        // Total: 3.600.000 - 50.000 + 25.000 = 3.575.000
        $this->assertEquals(3575000, (int) $quotation->total_amount);
        $this->assertCount(2, $quotation->items);

        $response->assertRedirect(route('quotations.show', $quotation));
        $response->assertSessionHas('success');
        $response->assertSessionHas('wa_quotation_url');
    }

    public function test_customer_can_view_public_quotation_page(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Siti Aminah']);

        $quotation = Quotation::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-0001',
            'title' => 'Seragam Kantor 100 Pcs',
            'status' => 'sent',
            'valid_until' => now()->addDays(7)->toDateString(),
            'subtotal' => 10000000,
            'total_amount' => 10000000,
        ]);

        $quotation->items()->create([
            'item_name' => 'Kemeja Drill',
            'quantity' => 100,
            'unit_price' => 100000,
            'total_price' => 10000000,
        ]);

        $response = $this->get(route('quotations.public', $quotation->public_token));
        $response->assertOk();
        $response->assertSee('Seragam Kantor 100 Pcs');
        $response->assertSee('QUO-2026-0001');
        $response->assertSee('Sablon Bintang');
        $response->assertSee('Kemeja Drill');
    }

    public function test_customer_can_approve_quotation_online(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Doni Kusuma']);

        $quotation = Quotation::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-0002',
            'title' => 'Polo Shirt Bordir',
            'status' => 'sent',
            'valid_until' => now()->addDays(7)->toDateString(),
            'total_amount' => 2500000,
        ]);

        $response = $this->post(route('quotations.public.approve', $quotation->public_token), [
            'approved_by_name' => 'Doni Kusuma (Manager)',
            'approval_notes' => 'Disetujui untuk diproduksi, DP akan ditransfer siang ini.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $quotation->refresh();
        $this->assertSame(Quotation::STATUS_APPROVED, $quotation->status);
        $this->assertNotNull($quotation->approved_at);
        $this->assertSame('Doni Kusuma (Manager)', $quotation->approved_by_name);
        $this->assertStringContainsString('Doni Kusuma', $quotation->notes);
    }

    public function test_customer_can_reject_quotation_with_reason(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Rina Wijaya']);

        $quotation = Quotation::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-0003',
            'title' => 'Tas Spunbond Custom',
            'status' => 'sent',
            'valid_until' => now()->addDays(7)->toDateString(),
            'total_amount' => 800000,
        ]);

        $response = $this->post(route('quotations.public.reject', $quotation->public_token), [
            'rejection_reason' => 'Harga melebihi alokasi anggaran kepanitiaan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $quotation->refresh();
        $this->assertSame(Quotation::STATUS_REJECTED, $quotation->status);
        $this->assertNotNull($quotation->rejected_at);
        $this->assertSame('Harga melebihi alokasi anggaran kepanitiaan.', $quotation->rejection_reason);
    }

    public function test_approved_quotation_can_be_converted_into_order(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Eko Prasetyo']);

        $quotation = Quotation::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-0004',
            'title' => 'Kaos Reuni Akbar 200 Pcs',
            'status' => Quotation::STATUS_APPROVED,
            'valid_until' => now()->addDays(7)->toDateString(),
            'subtotal' => 12000000,
            'total_amount' => 12000000,
            'notes' => 'Warna Navy Cotton Combed 24s',
        ]);

        $quotation->items()->create([
            'item_name' => 'Kaos Reuni',
            'quantity' => 200,
            'unit_price' => 60000,
            'total_price' => 12000000,
        ]);

        $response = $this->actingAs($owner)->post(route('quotations.convert', $quotation));

        $order = Order::where('user_id', $owner->id)->first();
        $this->assertNotNull($order);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('Kaos Reuni Akbar 200 Pcs', $order->name);
        $this->assertEquals(200, $order->quantity);
        $this->assertEquals(12000000, (int) $order->total_amount);
        $this->assertSame('new', $order->status);

        $quotation->refresh();
        $this->assertSame(Quotation::STATUS_CONVERTED, $quotation->status);
        $this->assertSame($order->id, $quotation->order_id);

        $response->assertRedirect(route('orders.show', $order));
    }

    public function test_cannot_convert_another_tenants_quotation(): void
    {
        $tenantA = $this->createOwnerWithPlan();
        $tenantB = $this->createOwnerWithPlan();

        $customerB = Customer::create(['user_id' => $tenantB->id, 'name' => 'Pelanggan Toko B']);
        $quotationB = Quotation::create([
            'user_id' => $tenantB->id,
            'customer_id' => $customerB->id,
            'quotation_number' => 'QUO-B-001',
            'title' => 'Order Rahasia Toko B',
            'status' => 'approved',
            'total_amount' => 5000000,
        ]);

        // Tenant A tries to convert Tenant B's quote
        $response = $this->actingAs($tenantA)->post(route('quotations.convert', $quotationB));
        $response->assertForbidden();

        $this->assertDatabaseMissing('orders', ['user_id' => $tenantA->id]);
    }

    public function test_whatsapp_service_generates_valid_quotation_url_and_message(): void
    {
        $owner = $this->createOwnerWithPlan();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Budi', 'phone' => '08123456789']);

        $quotation = Quotation::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QUO-2026-9999',
            'title' => 'Seragam Olahraga',
            'total_amount' => 5000000,
            'status' => 'draft',
        ]);

        $service = app(WhatsAppService::class);
        $message = $service->quotationMessage($quotation);
        $url = $service->quotationUrl($quotation);

        $this->assertStringContainsString('Seragam Olahraga', $message);
        $this->assertStringContainsString('QUO-2026-9999', $message);
        $this->assertStringContainsString($quotation->public_url, $message);
        $this->assertStringStartsWith('https://wa.me/628123456789?text=', $url);
    }
}
