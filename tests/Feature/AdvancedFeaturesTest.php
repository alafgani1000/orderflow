<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * FEATURE 1: Papan Kanban (Workshop Board & Drag-Drop AJAX)
     */
    public function test_owner_can_access_kanban_board(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Pelanggan Sablon',
            'phone'   => '08123456789',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-KNB-001',
            'name'           => 'Jersey Futsal',
            'quantity'       => 12,
            'price_per_unit' => 75000,
            'total_amount'   => 900000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($owner)->get(route('orders.kanban'));
        $response->assertOk();
        $response->assertSee('Papan Workshop');
        $response->assertSee('ORD-KNB-001');
        $response->assertSee('Jersey Futsal');
    }

    public function test_kanban_drag_and_drop_ajax_updates_order_status(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Pelanggan Sablon',
            'phone'   => '08123456789',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-KNB-002',
            'name'           => 'Polo Shirt Bordir',
            'quantity'       => 24,
            'price_per_unit' => 60000,
            'total_amount'   => 1440000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($owner)->patchJson(route('orders.update-status', $order), [
            'status' => 'production',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status'  => 'production',
        ]);

        $this->assertSame('production', $order->fresh()->status);
    }

    /**
     * FEATURE 2: Rincian Varian / Ukuran (Size Breakdown)
     */
    public function test_can_create_order_with_size_breakdown(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Komunitas Gowes',
            'phone'   => '08129876543',
        ]);

        $sizes = [
            'S'  => 5,
            'M'  => 10,
            'L'  => 15,
            'XL' => 5,
        ];

        $response = $this->actingAs($owner)->post(route('orders.store'), [
            'customer_id'    => $customer->id,
            'name'           => 'Jersey Custom Printing',
            'quantity'       => 35,
            'price_per_unit' => 100000,
            'total_amount'   => 3500000,
            'status'         => 'new',
            'size_breakdown' => $sizes,
        ]);

        $response->assertRedirect();
        
        $order = Order::where('name', 'Jersey Custom Printing')->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->has_size_breakdown);
        $this->assertEquals($sizes, $order->size_breakdown);
        $this->assertStringContainsString('S: 5', $order->size_summary);
        $this->assertStringContainsString('M: 10', $order->size_summary);
        $this->assertStringContainsString('L: 15', $order->size_summary);
        $this->assertStringContainsString('XL: 5', $order->size_summary);

        // Verify printed on invoice
        $invoiceResponse = $this->actingAs($owner)->get(route('orders.invoice', $order));
        $invoiceResponse->assertOk();
        $invoiceResponse->assertSee('Rincian Ukuran:');
        $invoiceResponse->assertSee('S: 5');

        // Verify displayed on public tracking page
        $trackingResponse = $this->get(route('orders.track', $order->tracking_token));
        $trackingResponse->assertOk();
        $trackingResponse->assertSee('Rincian Ukuran');
        $trackingResponse->assertSee('S:');
        $trackingResponse->assertSee('5 pcs');
    }

    /**
     * FEATURE 3: Laporan Keuangan & Export Excel (.csv)
     */
    public function test_owner_can_view_financial_report_and_metrics(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'PT Karya Bersama',
            'phone'   => '08123444555',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-FIN-001',
            'name'           => 'Seragam Lapangan',
            'quantity'       => 50,
            'price_per_unit' => 120000,
            'total_amount'   => 6000000,
            'status'         => 'completed',
        ]);

        Payment::create([
            'order_id'     => $order->id,
            'amount'       => 4000000,
            'payment_date' => now()->toDateString(),
            'method'       => 'transfer',
        ]);

        $response = $this->actingAs($owner)->get(route('reports.index'));
        $response->assertOk();
        $response->assertSee('Laporan Keuangan');
        $response->assertSee('Total Omset');
        $response->assertSee('Rp 6.000.000');
        $response->assertSee('Kas Masuk (Real)');
        $response->assertSee('Rp 4.000.000');
        $response->assertSee('Sisa Piutang');
        $response->assertSee('Rp 2.000.000');
    }

    public function test_owner_can_export_financial_report_csv_with_utf8_bom(): void
    {
        $owner = User::factory()->create([
            'role'          => User::ROLE_OWNER,
            'business_name' => 'Juara Sablon',
        ]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'PT Maju Terus',
        ]);

        Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-EXP-001',
            'name'           => 'Tote Bag Kanvas',
            'quantity'       => 100,
            'price_per_unit' => 15000,
            'total_amount'   => 1500000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($owner)->get(route('reports.export'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        // Check UTF-8 BOM
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        $this->assertStringStartsWith($bom, $content);
        $this->assertStringContainsString('ORD-EXP-001', $content);
        $this->assertStringContainsString('Tote Bag Kanvas', $content);
    }

    /**
     * FEATURE 4: Multi-Role Karyawan & Hak Akses
     */
    public function test_owner_can_add_and_delete_employee(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        // Add employee
        $storeResponse = $this->actingAs($owner)->post(route('employees.store'), [
            'name'     => 'Budi Operator',
            'email'    => 'budi.sablon@test.com',
            'role'     => User::ROLE_PRODUCTION,
            'password' => 'password123',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email'    => 'budi.sablon@test.com',
            'role'     => User::ROLE_PRODUCTION,
            'owner_id' => $owner->id,
        ]);

        $employee = User::where('email', 'budi.sablon@test.com')->first();
        $this->assertSame($owner->id, $employee->getStoreOwnerId());
        $this->assertTrue($employee->isProduction());
        $this->assertFalse($employee->canViewFinances());

        // Delete employee
        $destroyResponse = $this->actingAs($owner)->delete(route('employees.destroy', $employee));
        $destroyResponse->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_production_role_cannot_view_or_export_finances(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $operator = User::factory()->create([
            'role'     => User::ROLE_PRODUCTION,
            'owner_id' => $owner->id,
        ]);

        // Cannot view reports
        $reportResponse = $this->actingAs($operator)->get(route('reports.index'));
        $reportResponse->assertForbidden();

        // Cannot export reports
        $exportResponse = $this->actingAs($operator)->get(route('reports.export'));
        $exportResponse->assertForbidden();

        // Cannot view payments
        $paymentResponse = $this->actingAs($operator)->get(route('payments.index'));
        $paymentResponse->assertForbidden();
    }

    public function test_production_role_cannot_manage_employees(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $operator = User::factory()->create([
            'role'     => User::ROLE_PRODUCTION,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($operator)->post(route('employees.store'), [
            'name'     => 'Hacker',
            'email'    => 'hacker@test.com',
            'role'     => User::ROLE_ADMIN_CS,
            'password' => 'password123',
        ]);

        $response->assertForbidden();
    }
}
