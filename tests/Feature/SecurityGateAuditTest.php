<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\SecureFileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityGateAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('orderflow.storage.private_disk', 'local');
    }

    public function test_unverified_email_user_is_redirected_to_verification_notice(): void
    {
        $unverifiedUser = User::factory()->unverified()->create([
            'role' => User::ROLE_OWNER,
        ]);

        $response = $this->actingAs($unverifiedUser)->get(route('dashboard'));
        $response->assertRedirect(route('verification.notice'));

        $ordersResponse = $this->actingAs($unverifiedUser)->get(route('orders.index'));
        $ordersResponse->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_protected_routes(): void
    {
        $verifiedUser = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($verifiedUser)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_design_files_are_stored_on_private_disk_and_not_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Pelanggan']);
        $order = Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-SEC-01',
            'name' => 'Baju Sablon',
            'quantity' => 10,
            'price_per_unit' => 50000,
            'total_amount' => 500000,
            'status' => 'new',
        ]);

        $file = UploadedFile::fake()->create('design.png', 200, 'image/png');
        $this->actingAs($owner)->post(route('orders.files.store', $order), [
            'files' => [$file],
        ]);

        $orderFile = OrderFile::where('order_id', $order->id)->first();
        $this->assertNotNull($orderFile);

        // Harus ada di disk private (local), BUKAN di disk public
        Storage::disk('local')->assertExists($orderFile->file_path);
        Storage::disk('public')->assertMissing($orderFile->file_path);
    }

    public function test_design_file_download_is_strictly_authorized(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $stranger = User::factory()->create(['role' => User::ROLE_OWNER]);

        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Pelanggan']);
        $order = Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-SEC-02',
            'name' => 'Spanduk',
            'quantity' => 1,
            'price_per_unit' => 50000,
            'total_amount' => 50000,
            'status' => 'new',
        ]);

        $storedPath = app(SecureFileStorage::class)->store(
            UploadedFile::fake()->create('rahasia.pdf', 100, 'application/pdf'),
            "designs/{$order->id}"
        );

        $orderFile = $order->files()->create([
            'file_name' => 'rahasia.pdf',
            'file_path' => $storedPath,
            'file_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        // Orang asing tidak boleh download (403)
        $this->actingAs($stranger)
            ->get(route('orders.files.download', [$order, $orderFile]))
            ->assertForbidden();

        // Pemilik boleh download (200) dan header aman
        $response = $this->actingAs($owner)->get(route('orders.files.download', [$order, $orderFile]));
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_payment_proof_is_private_and_isolated_between_tenants(): void
    {
        Storage::fake('local');
        $ownerA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $ownerB = User::factory()->create(['role' => User::ROLE_OWNER]);

        $plan = Plan::create([
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

        $proofPath = app(SecureFileStorage::class)->store(
            UploadedFile::fake()->create('bukti.jpg', 150, 'image/jpeg'),
            "subscription-proofs/{$ownerA->id}"
        );

        $invoice = SubscriptionInvoice::create([
            'user_id' => $ownerA->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-TEST-001',
            'amount' => 49000,
            'payment_method' => 'Transfer BCA',
            'status' => 'pending',
            'payment_proof' => $proofPath,
        ]);

        // Owner B tidak boleh melihat bukti Owner A (404/not found to avoid ID enumeration)
        $this->actingAs($ownerB)
            ->get(route('billing.invoices.proof', $invoice))
            ->assertNotFound();

        // Owner A dapat melihat buktinya sendiri
        $this->actingAs($ownerA)
            ->get(route('billing.invoices.proof', $invoice))
            ->assertOk();
    }

    public function test_superadmin_can_view_payment_proof(): void
    {
        Storage::fake('local');
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);

        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Pro',
            'price' => 49000,
            'billing_period' => 'monthly',
            'is_active' => true,
        ]);

        $proofPath = app(SecureFileStorage::class)->store(
            UploadedFile::fake()->create('bukti_admin.pdf', 100, 'application/pdf'),
            "subscription-proofs/{$owner->id}"
        );

        $invoice = SubscriptionInvoice::create([
            'user_id' => $owner->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-ADM-001',
            'amount' => 49000,
            'payment_method' => 'Transfer BCA',
            'status' => 'pending',
            'payment_proof' => $proofPath,
        ]);

        // Regular owner cannot access admin proof route
        $this->actingAs($owner)
            ->get(route('admin.payments.proof', $invoice))
            ->assertForbidden();

        // Super Admin can access proof
        $this->actingAs($superAdmin)
            ->get(route('admin.payments.proof', $invoice))
            ->assertOk();
    }

    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson(route('health'));
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => ['database', 'storage', 'cache', 'backup'],
        ]);
    }

    public function test_backup_and_secure_files_artisan_commands(): void
    {
        $backupDir = storage_path('framework/testing/backups');
        config()->set('orderflow.backup.path', $backupDir);
        File::deleteDirectory($backupDir);

        $this->artisan('orderflow:backup')
            ->assertSuccessful();

        $this->assertTrue(File::isDirectory($backupDir));
        $zips = File::glob($backupDir . '/*.zip');
        $this->assertNotEmpty($zips);

        $this->artisan('orderflow:secure-files', ['--dry-run' => true])
            ->assertSuccessful();

        File::deleteDirectory($backupDir);
    }
}
