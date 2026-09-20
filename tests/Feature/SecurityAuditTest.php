<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * VULN 1 TEST: Cross-Tenant Customer IDOR Injection
     */
    public function test_cannot_create_order_with_another_tenants_customer(): void
    {
        $tenantA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $tenantB = User::factory()->create(['role' => User::ROLE_OWNER]);

        // Customer belongs to Tenant B
        $customerB = Customer::create([
            'user_id' => $tenantB->id,
            'name'    => 'Pelanggan Rahasia Toko B',
            'phone'   => '081299990000',
        ]);

        // Tenant A tries to create an order referencing Customer B
        $response = $this->actingAs($tenantA)->post(route('orders.store'), [
            'customer_id'    => $customerB->id,
            'name'           => 'Pesanan Siluman',
            'quantity'       => 10,
            'price_per_unit' => 50000,
            'total_amount'   => 500000,
            'status'         => 'new',
        ]);

        $response->assertSessionHasErrors('customer_id');
        $this->assertDatabaseMissing('orders', ['name' => 'Pesanan Siluman']);
    }

    public function test_cannot_update_order_to_another_tenants_customer(): void
    {
        $tenantA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $tenantB = User::factory()->create(['role' => User::ROLE_OWNER]);

        $customerA = Customer::create([
            'user_id' => $tenantA->id,
            'name'    => 'Pelanggan Toko A',
        ]);

        $customerB = Customer::create([
            'user_id' => $tenantB->id,
            'name'    => 'Pelanggan Toko B',
        ]);

        $orderA = Order::create([
            'user_id'        => $tenantA->id,
            'customer_id'    => $customerA->id,
            'order_number'   => 'ORD-SEC-001',
            'name'           => 'Order Milik Toko A',
            'quantity'       => 5,
            'price_per_unit' => 20000,
            'total_amount'   => 100000,
            'status'         => 'new',
        ]);

        // Tenant A tries to change customer to Customer B
        $response = $this->actingAs($tenantA)->put(route('orders.update', $orderA), [
            'customer_id'    => $customerB->id,
            'name'           => 'Order Milik Toko A',
            'quantity'       => 5,
            'price_per_unit' => 20000,
            'total_amount'   => 100000,
            'status'         => 'new',
        ]);

        $response->assertSessionHasErrors('customer_id');
        $this->assertSame($customerA->id, $orderA->fresh()->customer_id);
    }

    /**
     * VULN 2 TEST: Operator Financial Price Tampering Prevention
     */
    public function test_operator_cannot_tamper_order_price_during_update(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $operator = User::factory()->create([
            'role'     => User::ROLE_PRODUCTION,
            'owner_id' => $owner->id,
        ]);

        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => 'Pelanggan Juara',
        ]);

        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-SEC-002',
            'name'           => 'Jaket Bomber',
            'quantity'       => 20,
            'price_per_unit' => 150000,
            'total_amount'   => 3000000,
            'status'         => 'waiting_design',
        ]);

        // Operator submits update attempting to set price and total to 0
        $response = $this->actingAs($operator)->put(route('orders.update', $order), [
            'customer_id'    => $customer->id,
            'name'           => 'Jaket Bomber (Revisi Desain)',
            'quantity'       => 20,
            'price_per_unit' => 0,
            'total_amount'   => 0,
            'status'         => 'production',
        ]);

        $response->assertRedirect();
        
        $refreshedOrder = $order->fresh();
        $this->assertSame('Jaket Bomber (Revisi Desain)', $refreshedOrder->name);
        $this->assertSame('production', $refreshedOrder->status);
        // Financial values MUST NOT be zeroed out
        $this->assertEquals(150000, (int)$refreshedOrder->price_per_unit);
        $this->assertEquals(3000000, (int)$refreshedOrder->total_amount);
    }

    /**
     * VULN 3 TEST: CSV Formula Injection Sanitization (CWE-1236)
     */
    public function test_csv_export_neutralizes_spreadsheet_formula_injection(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name'    => '=cmd|\' /C calc\'!A0', // Dangerous formula payload
        ]);

        Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-CSV-001',
            'name'           => '@SUM(1+1)', // Another formula payload
            'quantity'       => 10,
            'price_per_unit' => 10000,
            'total_amount'   => 100000,
            'status'         => 'new',
        ]);

        $response = $this->actingAs($owner)->get(route('reports.export'));
        $response->assertOk();

        $csv = $response->streamedContent();
        
        // Formulas MUST be escaped with a leading single quote (')
        $this->assertStringContainsString("'=cmd|", $csv);
        $this->assertStringContainsString("'@SUM", $csv);
    }

    /**
     * VULN 4 TEST: Public Tracking Rate Limiting
     */
    public function test_public_tracking_has_rate_limiting_headers(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Budi']);
        $order = Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => 'ORD-TRACK-001',
            'name'           => 'Kaos Promosi',
            'quantity'       => 10,
            'price_per_unit' => 50000,
            'total_amount'   => 500000,
            'status'         => 'new',
        ]);

        $response = $this->get(route('orders.track', $order->tracking_token));
        $response->assertOk();
        $response->assertHeader('X-RateLimit-Limit', 60);
    }
}
