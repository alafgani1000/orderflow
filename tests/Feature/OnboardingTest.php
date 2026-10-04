<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_onboarding_in_their_preferred_language(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'locale' => 'en',
        ]);

        $this->actingAs($owner)
            ->get(route('onboarding.show'))
            ->assertOk()
            ->assertSee('Set Up Your Store')
            ->assertSee('Store Payment Account');
    }

    public function test_staff_cannot_manage_store_onboarding(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $staff = User::factory()->create([
            'role' => User::ROLE_ADMIN_CS,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($staff)
            ->get(route('onboarding.show'))
            ->assertForbidden();
    }

    public function test_owner_can_complete_store_profile_onboarding(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        $response = $this->actingAs($owner)->put(route('onboarding.update'), [
            'business_name' => 'Sablon Juara',
            'phone' => '08123456789',
            'business_address' => 'Jl. Produksi No. 10, Bandung',
            'business_bank_name' => 'BCA',
            'business_bank_account' => '1234567890',
            'business_bank_holder' => 'Sablon Juara',
        ]);

        $response->assertRedirect(route('dashboard'));

        $owner->refresh();
        $this->assertSame('Sablon Juara', $owner->business_name);
        $this->assertSame('Jl. Produksi No. 10, Bandung', $owner->business_address);
        $this->assertSame('1234567890', $owner->business_bank_account);
        $this->assertNotNull($owner->onboarding_completed_at);
    }

    public function test_onboarding_requires_complete_bank_details_when_one_is_filled(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        $this->actingAs($owner)->put(route('onboarding.update'), [
            'business_name' => 'Sablon Juara',
            'phone' => '08123456789',
            'business_bank_name' => 'BCA',
        ])->assertSessionHasErrors([
            'business_bank_account',
            'business_bank_holder',
        ]);

        $this->assertNull($owner->fresh()->onboarding_completed_at);
    }

    public function test_dashboard_checklist_tracks_activation_and_disappears_when_complete(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'business_name' => 'Sablon Juara',
            'phone' => '08123456789',
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Checklist Aktivasi Toko')
            ->assertSee('Tambahkan pelanggan pertama');

        $customer = Customer::create([
            'user_id' => $owner->id,
            'name' => 'Pelanggan Pertama',
        ]);

        Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-0001',
            'name' => 'Kaos Komunitas',
            'quantity' => 20,
            'price_per_unit' => 50000,
            'total_amount' => 1000000,
            'status' => 'new',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Checklist Aktivasi Toko');
    }

    public function test_invoice_uses_store_address_and_payment_account(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'business_name' => 'Sablon Juara',
            'business_address' => 'Jl. Produksi No. 10, Bandung',
            'business_bank_name' => 'BCA',
            'business_bank_account' => '1234567890',
            'business_bank_holder' => 'Sablon Juara',
        ]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name' => 'Pelanggan Pertama',
        ]);
        $order = Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-0001',
            'name' => 'Kaos Komunitas',
            'quantity' => 20,
            'price_per_unit' => 50000,
            'total_amount' => 1000000,
            'status' => 'new',
        ]);

        $this->actingAs($owner)
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('Jl. Produksi No. 10, Bandung')
            ->assertSee('1234567890')
            ->assertSee('Informasi Pembayaran');
    }
}
