<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SaaS Feature Test Suite
 *
 * Memverifikasi seluruh fitur multi-tenant SaaS:
 * - Isolasi nomor order per tenant
 * - Auto free-trial saat registrasi
 * - Kuota pesanan dan staf berdasarkan paket
 * - Akses Super Admin panel
 * - Alur verifikasi pembayaran langganan
 */
class SaaSTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function createPlan(string $slug, array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'name'                 => ucfirst($slug),
            'slug'                 => $slug,
            'description'          => 'Test plan',
            'price'                => 0,
            'billing_period'       => 'monthly',
            'max_orders_per_month' => null,
            'max_employees'        => null,
            'features'             => [],
            'is_popular'           => false,
            'is_active'            => true,
        ], $overrides));
    }

    private function createOwner(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => User::ROLE_OWNER], $overrides));
    }

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role'  => User::ROLE_SUPERADMIN,
            'email' => 'sa@test.test',
        ]);
    }

    private function subscribeOwner(User $owner, Plan $plan, string $status = Subscription::STATUS_ACTIVE): Subscription
    {
        return Subscription::create([
            'user_id'        => $owner->id,
            'plan_id'        => $plan->id,
            'status'         => $status,
            'starts_at'      => now(),
            'ends_at'        => $status === Subscription::STATUS_ACTIVE ? now()->addMonth() : null,
            'trial_ends_at'  => $status === Subscription::STATUS_TRIALING ? now()->addDays(14) : null,
        ]);
    }

    private function makeOrder(User $owner, Customer $customer, string $orderNumber = 'ORD-0001'): Order
    {
        return Order::create([
            'user_id'        => $owner->id,
            'customer_id'    => $customer->id,
            'order_number'   => $orderNumber,
            'name'           => 'Test Order',
            'quantity'       => 10,
            'price_per_unit' => 5000,
            'total_amount'   => 50000,
            'status'         => 'new',
        ]);
    }

    // =========================================================================
    // TEST 1: Isolasi Nomor Order Per Tenant
    // =========================================================================

    public function test_order_number_uniqueness_is_isolated_per_tenant(): void
    {
        $tenantA = $this->createOwner();
        $tenantB = $this->createOwner();

        $customerA = Customer::create(['user_id' => $tenantA->id, 'name' => 'Pelanggan A']);
        $customerB = Customer::create(['user_id' => $tenantB->id, 'name' => 'Pelanggan B']);

        // Kedua toko berbeda dapat memiliki nomor order ORD-0001 tanpa konflik
        $orderA = $this->makeOrder($tenantA, $customerA, 'ORD-0001');
        $orderB = $this->makeOrder($tenantB, $customerB, 'ORD-0001');

        $this->assertDatabaseHas('orders', [
            'user_id'      => $tenantA->id,
            'order_number' => 'ORD-0001',
        ]);
        $this->assertDatabaseHas('orders', [
            'user_id'      => $tenantB->id,
            'order_number' => 'ORD-0001',
        ]);

        // Tapi dalam satu toko, nomor order harus unik
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->makeOrder($tenantA, $customerA, 'ORD-0001');
    }

    // =========================================================================
    // TEST 2: Auto Free Trial 14 Hari Saat Registrasi
    // =========================================================================

    public function test_new_registered_user_automatically_receives_14_days_trial(): void
    {
        // Buat plan Pro agar trial bisa diberikan
        $this->createPlan('pro', [
            'name'  => 'Pro Juragan',
            'price' => 49000,
        ]);

        $response = $this->post('/register', [
            'name'                  => 'Pemilik Baru',
            'business_name'         => 'Toko Baru Sablon',
            'phone'                 => '081200001111',
            'email'                 => 'pemilik@baru.test',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'pemilik@baru.test')->first();

        // Harus memiliki subscription trial
        $subscription = $user->subscription;
        $this->assertNotNull($subscription, 'User baru harus mendapatkan subscription trial otomatis');
        $this->assertEquals(Subscription::STATUS_TRIALING, $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->trial_ends_at->isFuture());
        $this->assertGreaterThanOrEqual(13, $subscription->days_remaining);
    }

    // =========================================================================
    // TEST 3: Penegakan Batas Kuota Pesanan Paket Starter
    // =========================================================================

    public function test_monthly_order_limit_is_enforced_for_starter_plan(): void
    {
        $starterPlan = $this->createPlan('starter', [
            'name'                 => 'Starter',
            'max_orders_per_month' => 3, // batas kecil untuk test
        ]);

        $owner    = $this->createOwner();
        $this->subscribeOwner($owner, $starterPlan);
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Pelanggan Test']);

        // Buat 3 pesanan (sampai batas)
        for ($i = 1; $i <= 3; $i++) {
            $this->makeOrder($owner, $customer, "ORD-{$i}");
        }

        // Cek method helper kuota
        $this->assertFalse($owner->canCreateOrder(), 'canCreateOrder harus false setelah kuota penuh');

        // Coba buat order ke-4 via HTTP → harus diarahkan balik dengan pesan error
        $response = $this->actingAs($owner)->get(route('orders.create'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error');

        $response2 = $this->actingAs($owner)->post(route('orders.store'), [
            'customer_id'    => $customer->id,
            'name'           => 'Order Melebihi Kuota',
            'quantity'       => 10,
            'price_per_unit' => 5000,
            'total_amount'   => 50000,
            'status'         => 'new',
        ]);
        $response2->assertRedirect(route('orders.index'));
        $response2->assertSessionHas('error');
        $this->assertDatabaseMissing('orders', ['name' => 'Order Melebihi Kuota']);
    }

    // =========================================================================
    // TEST 4: Penegakan Batas Kuota Staf Paket Starter
    // =========================================================================

    public function test_employee_limit_is_enforced_for_starter_plan(): void
    {
        $starterPlan = $this->createPlan('starter', [
            'name'          => 'Starter',
            'max_employees' => 1, // maks 1 staf
        ]);

        $owner = $this->createOwner();
        $this->subscribeOwner($owner, $starterPlan);

        // Tambah 1 staf (sampai batas)
        User::create([
            'name'     => 'Staf Pertama',
            'email'    => 'staf1@test.test',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_ADMIN_CS,
            'owner_id' => $owner->id,
        ]);

        $this->assertFalse($owner->fresh()->canAddEmployee(), 'canAddEmployee harus false setelah kuota staf penuh');

        // Coba tambah staf ke-2 via HTTP → harus ditolak
        $response = $this->actingAs($owner)->post(route('employees.store'), [
            'name'     => 'Staf Kedua Gagal',
            'email'    => 'staf2@test.test',
            'role'     => User::ROLE_PRODUCTION,
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'staf2@test.test']);
    }

    // =========================================================================
    // TEST 5: Super Admin Dapat Mengakses Panel Admin
    // =========================================================================

    public function test_superadmin_can_access_admin_panel(): void
    {
        $this->createPlan('pro');
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response2 = $this->actingAs($superAdmin)->get(route('admin.tenants.index'));
        $response2->assertStatus(200);

        $response3 = $this->actingAs($superAdmin)->get(route('admin.payments.index'));
        $response3->assertStatus(200);
    }

    // =========================================================================
    // TEST 6: Owner Biasa Tidak Dapat Mengakses Panel Super Admin
    // =========================================================================

    public function test_regular_owner_cannot_access_superadmin_panel(): void
    {
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    // =========================================================================
    // TEST 7: Alur Submit Bukti Bayar → Approval Super Admin → Subscription Aktif
    // =========================================================================

    public function test_subscription_payment_submission_and_superadmin_approval(): void
    {
        $proPlan    = $this->createPlan('pro', ['name' => 'Pro Juragan', 'price' => 49000]);
        $owner      = $this->createOwner();
        $superAdmin = $this->createSuperAdmin();

        // Owner submit konfirmasi pembayaran (tanpa file upload di test)
        $response = $this->actingAs($owner)->post(route('billing.confirm'), [
            'plan_id'        => $proPlan->id,
            'payment_method' => 'Transfer BCA',
            'notes'          => 'Transfer dari rekening a.n. Test Owner',
        ]);

        $response->assertRedirect(route('billing.index'));
        $response->assertSessionHas('success');

        // Invoice harus tercatat dengan status pending
        $invoice = SubscriptionInvoice::where('user_id', $owner->id)->first();
        $this->assertNotNull($invoice, 'Invoice harus terbuat setelah konfirmasi bayar');
        $this->assertEquals(SubscriptionInvoice::STATUS_PENDING, $invoice->status);
        $this->assertStringStartsWith('INV-SUB-', $invoice->invoice_number);

        // Super Admin approve pembayaran → subscription owner harus aktif
        $approveResponse = $this->actingAs($superAdmin)->post(route('admin.payments.approve', $invoice));
        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        // Cek invoice sekarang paid
        $invoice->refresh();
        $this->assertEquals(SubscriptionInvoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        // Cek subscription owner sekarang active
        $owner->refresh();
        $subscription = $owner->currentSubscription();
        $this->assertNotNull($subscription);
        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertTrue($subscription->isActive());
    }

    // =========================================================================
    // TEST 8: Halaman Billing Dapat Diakses Owner
    // =========================================================================

    public function test_owner_can_access_billing_page(): void
    {
        $plan  = $this->createPlan('pro', ['name' => 'Pro Juragan', 'price' => 49000]);
        $owner = $this->createOwner();
        $this->subscribeOwner($owner, $plan);

        $response = $this->actingAs($owner)->get(route('billing.index'));
        $response->assertStatus(200);
        $response->assertSee('Langganan');
    }

    // =========================================================================
    // TEST 9: Tenant Unlimited Plan Tidak Terblokir Kuota
    // =========================================================================

    public function test_unlimited_plan_never_blocks_order_creation(): void
    {
        $proPlan  = $this->createPlan('pro', ['name' => 'Pro', 'max_orders_per_month' => null]);
        $owner    = $this->createOwner();
        $this->subscribeOwner($owner, $proPlan);
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Pelanggan Pro']);

        // Buat banyak order tanpa batas
        for ($i = 1; $i <= 50; $i++) {
            $this->makeOrder($owner, $customer, "ORD-{$i}");
        }

        $this->assertTrue($owner->canCreateOrder(), 'Paket unlimited tidak boleh memblokir pembuatan order');
    }

    // =========================================================================
    // TEST 10: Super Admin Dapat Mengubah Paket dan Status Langganan Tenant
    // =========================================================================

    public function test_superadmin_can_update_tenant_subscription(): void
    {
        $starterPlan = $this->createPlan('starter', ['name' => 'Starter', 'price' => 0]);
        $proPlan     = $this->createPlan('pro', ['name' => 'Pro Juragan', 'price' => 49000]);
        $owner       = $this->createOwner();
        $superAdmin  = $this->createSuperAdmin();

        $this->subscribeOwner($owner, $starterPlan);

        // SuperAdmin upgrade tenant ke Pro
        $response = $this->actingAs($superAdmin)->post(
            route('admin.tenants.subscription', $owner),
            [
                'plan_id'     => $proPlan->id,
                'status'      => Subscription::STATUS_ACTIVE,
                'extend_days' => 30,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $owner->refresh();
        $subscription = $owner->currentSubscription();
        $this->assertEquals($proPlan->id, $subscription->plan_id);
        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertTrue($subscription->isActive());
    }
}
