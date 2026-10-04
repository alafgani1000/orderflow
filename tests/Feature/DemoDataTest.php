<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DemoDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_tagged_sample_dataset(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'locale' => 'en',
        ]);

        $response = $this->actingAs($owner)->post(route('demo-data.store'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', fn (string $message) => str_contains($message, '3 customers')
            && str_contains($message, '6 orders')
            && str_contains($message, '5 payments'));

        $this->assertSame(3, Customer::where('user_id', $owner->id)->whereNotNull('demo_batch_id')->count());
        $this->assertSame(6, Order::where('user_id', $owner->id)->whereNotNull('demo_batch_id')->count());
        $this->assertSame(5, Payment::whereNotNull('demo_batch_id')->count());
        $this->assertSame(1, Customer::where('user_id', $owner->id)->distinct()->count('demo_batch_id'));
        $this->assertDatabaseHas('customers', [
            'user_id' => $owner->id,
            'name' => 'Bandung Riders Community',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Explore OrderFlow with Sample Data')
            ->assertSee('Delete Sample Data');
    }

    public function test_creating_sample_data_is_idempotent(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        $this->actingAs($owner)->post(route('demo-data.store'))->assertRedirect(route('dashboard'));
        $this->actingAs($owner)
            ->post(route('demo-data.store'))
            ->assertSessionHas('error');

        $this->assertSame(3, Customer::where('user_id', $owner->id)->count());
        $this->assertSame(6, Order::where('user_id', $owner->id)->count());
    }

    public function test_deleting_samples_preserves_real_data_and_cross_tenant_data(): void
    {
        $ownerA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $ownerB = User::factory()->create(['role' => User::ROLE_OWNER]);
        $service = app(DemoDataService::class);
        $service->create($ownerA);
        $service->create($ownerB);

        $sampleCustomer = Customer::where('user_id', $ownerA->id)->whereNotNull('demo_batch_id')->firstOrFail();
        $realCustomer = Customer::create([
            'user_id' => $ownerA->id,
            'name' => 'Pelanggan Asli',
        ]);
        $realOrderOnSampleCustomer = $this->realOrder($ownerA, $sampleCustomer, 'REAL-001');
        $realOrder = $this->realOrder($ownerA, $realCustomer, 'REAL-002');

        $response = $this->actingAs($ownerA)->delete(route('demo-data.destroy'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertSame(0, Order::where('user_id', $ownerA->id)->whereNotNull('demo_batch_id')->count());
        $this->assertSame(0, Payment::whereHas('order', fn ($query) => $query->where('user_id', $ownerA->id))->whereNotNull('demo_batch_id')->count());
        $this->assertDatabaseHas('orders', ['id' => $realOrderOnSampleCustomer->id]);
        $this->assertDatabaseHas('orders', ['id' => $realOrder->id]);
        $this->assertDatabaseHas('customers', [
            'id' => $sampleCustomer->id,
            'demo_batch_id' => null,
        ]);
        $this->assertSame(6, Order::where('user_id', $ownerB->id)->whereNotNull('demo_batch_id')->count());
    }

    public function test_sample_orders_do_not_consume_monthly_plan_quota(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $plan = Plan::create([
            'name' => 'Starter Demo Test',
            'slug' => 'starter-demo-test',
            'price' => 0,
            'billing_period' => 'monthly',
            'max_orders_per_month' => 1,
            'max_employees' => 1,
            'features' => [],
            'is_popular' => false,
            'is_active' => true,
        ]);
        Subscription::create([
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
        app(DemoDataService::class)->create($owner);

        $this->assertTrue($owner->canCreateOrder());

        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Pelanggan Asli']);
        $this->realOrder($owner, $customer, 'REAL-001');

        $this->assertFalse($owner->canCreateOrder());
    }

    public function test_staff_cannot_create_or_delete_sample_data(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $staff = User::factory()->create([
            'role' => User::ROLE_ADMIN_CS,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($staff)->post(route('demo-data.store'))->assertForbidden();
        $this->actingAs($staff)->delete(route('demo-data.destroy'))->assertForbidden();
        $this->assertDatabaseCount('orders', 0);
    }

    private function realOrder(User $owner, Customer $customer, string $number): Order
    {
        return Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => $number,
            'name' => 'Pesanan Asli',
            'quantity' => 1,
            'price_per_unit' => 100000,
            'total_amount' => 100000,
            'status' => 'new',
        ]);
    }
}
