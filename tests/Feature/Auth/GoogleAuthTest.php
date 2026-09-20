<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createPlan(string $slug = 'pro'): Plan
    {
        return Plan::create([
            'name'                 => 'Pro Juragan',
            'slug'                 => $slug,
            'description'          => 'Paket Pro',
            'price'                => 49000,
            'billing_period'       => 'monthly',
            'max_orders_per_month' => null,
            'max_employees'        => null,
            'features'             => [],
            'is_popular'           => true,
            'is_active'            => true,
        ]);
    }

    public function test_google_redirect_redirects_to_google(): void
    {
        $response = $this->get(route('auth.google'));

        // Harus mengembalikan status redirect ke Google OAuth URL
        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_new_user_with_pro_trial(): void
    {
        $this->createPlan('pro');

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $abstractUser->shouldReceive('getName')->andReturn('Ahmad Google');
        $abstractUser->shouldReceive('getNickname')->andReturn('ahmad');
        $abstractUser->shouldReceive('getEmail')->andReturn('ahmad@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar/test.png');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Cek user terdaftar di database
        $user = User::where('email', 'ahmad@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Ahmad Google', $user->name);
        $this->assertEquals('google-unique-id-12345', $user->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar/test.png', $user->avatar);
        $this->assertEquals(User::ROLE_OWNER, $user->role);
        $this->assertNotNull($user->email_verified_at);

        // Cek subscription trial 14 hari
        $subscription = $user->subscription;
        $this->assertNotNull($subscription);
        $this->assertEquals(Subscription::STATUS_TRIALING, $subscription->status);
        $this->assertTrue($subscription->trial_ends_at->isFuture());
    }

    public function test_google_callback_logs_in_existing_google_user(): void
    {
        $existingUser = User::factory()->create([
            'email'     => 'existing@gmail.com',
            'google_id' => 'google-existing-999',
            'avatar'    => 'https://example.com/old-avatar.png',
            'role'      => User::ROLE_OWNER,
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-existing-999');
        $abstractUser->shouldReceive('getName')->andReturn($existingUser->name);
        $abstractUser->shouldReceive('getNickname')->andReturn(null);
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@gmail.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.png');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($existingUser);

        // Avatar harus diperbarui
        $existingUser->refresh();
        $this->assertEquals('https://example.com/new-avatar.png', $existingUser->avatar);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        // User yang sebelumnya daftar dengan email biasa tanpa google_id
        $manualUser = User::factory()->create([
            'email'     => 'manual@toko.com',
            'google_id' => null,
            'role'      => User::ROLE_OWNER,
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-link-888');
        $abstractUser->shouldReceive('getName')->andReturn($manualUser->name);
        $abstractUser->shouldReceive('getNickname')->andReturn(null);
        $abstractUser->shouldReceive('getEmail')->andReturn('manual@toko.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar/linked.png');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($manualUser);

        $manualUser->refresh();
        $this->assertEquals('google-link-888', $manualUser->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar/linked.png', $manualUser->avatar);
    }

    public function test_google_callback_handles_user_cancellation(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_callback_handles_provider_exception(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('OAuth state mismatch'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
