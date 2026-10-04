<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_change_the_interface_language(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update', 'en'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('locale', 'en');

        $this->withSession(['locale' => 'en'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back');
    }

    public function test_public_landing_page_is_translated(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Everything Your Workshop Needs in One Place')
            ->assertSee('Frequently Asked Questions')
            ->assertSee('Free 14-Day Trial');
    }

    public function test_unsupported_locale_returns_not_found(): void
    {
        $this->post('/language/fr')->assertNotFound();
    }

    public function test_authenticated_users_language_preference_is_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('locale.update', 'en'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('locale', 'en');

        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_saved_language_is_used_when_a_new_session_starts(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSee('Dashboard overview');
    }

    public function test_english_user_sees_translated_core_workflows(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Customer List');

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Order List');

        $this->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment Records');

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Financial &amp; Performance Reports', false);

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Interface Language');
    }

    public function test_english_user_sees_translated_billing_page(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Store Subscription &amp; Quotas', false)
            ->assertSee('OrderFlow Subscription Plans');
    }

    public function test_english_superadmin_sees_translated_admin_pages(): void
    {
        $admin = User::factory()->create([
            'locale' => 'en',
            'role' => User::ROLE_SUPERADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('SaaS Platform Control Center')
            ->assertSee('Manage All Stores');

        $this->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Subscription Payment Verification');

        $this->get(route('admin.tenants.index'))
            ->assertOk()
            ->assertSee('Store Management (Tenants)');
    }

    public function test_whatsapp_messages_follow_the_selected_language(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'John',
            'phone' => '081234567890',
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-EN-001',
            'name' => 'Team Shirts',
            'quantity' => 10,
            'price_per_unit' => 50000,
            'total_amount' => 500000,
            'status' => 'production',
        ]);

        app()->setLocale('en');
        $this->actingAs($user);

        $message = app(WhatsAppService::class)->statusMessage($order);

        $this->assertStringContainsString('Hello John', $message);
        $this->assertStringContainsString('Status: *Production*', $message);
        $this->assertStringContainsString('Remaining balance', $message);
    }

    public function test_validation_messages_follow_the_selected_language(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->post(route('customers.store'), [])
            ->assertSessionHasErrors([
                'name' => 'The name field is required.',
            ]);
    }
}
