<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_public_and_bilingual(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Syarat &amp; Ketentuan', false)
            ->assertSee('Hukum yang berlaku');

        $this->withSession(['locale' => 'en'])
            ->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Data retention');
    }

    public function test_checkout_uses_only_configured_payment_accounts(): void
    {
        config()->set('orderflow.billing.accounts.0.number', '111122223333');
        config()->set('orderflow.billing.accounts.0.holder', 'PT OrderFlow Valid');
        config()->set('orderflow.billing.accounts.1.number', null);
        config()->set('orderflow.billing.accounts.1.holder', null);

        $user = User::factory()->create();
        $plan = $this->createPaidPlan();

        $this->actingAs($user)
            ->get(route('billing.checkout', $plan))
            ->assertOk()
            ->assertSee('111122223333')
            ->assertSee('PT OrderFlow Valid')
            ->assertDontSee('8820-9182-3341')
            ->assertDontSee('131-00-9876543-2');
    }

    public function test_payment_confirmation_is_blocked_without_configured_method(): void
    {
        Storage::fake('public');
        config()->set('orderflow.billing.accounts', []);
        config()->set('orderflow.billing.qris_enabled', false);

        $user = User::factory()->create();
        $plan = $this->createPaidPlan();

        $this->actingAs($user)
            ->from(route('billing.checkout', $plan))
            ->post(route('billing.confirm'), [
                'plan_id' => $plan->id,
                'payment_method' => 'Transfer BCA',
                'payment_proof' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('billing.checkout', $plan))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount(SubscriptionInvoice::class, 0);
    }

    private function createPaidPlan(): Plan
    {
        return Plan::create([
            'name' => 'Pro Juragan',
            'slug' => 'pro',
            'description' => 'Paket Pro',
            'price' => 49000,
            'billing_period' => 'monthly',
            'max_orders_per_month' => null,
            'max_employees' => 5,
            'features' => [],
            'is_popular' => true,
            'is_active' => true,
        ]);
    }
}
