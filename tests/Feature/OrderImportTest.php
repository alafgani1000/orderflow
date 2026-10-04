<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\OrderNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_import_page_and_template_are_available_in_english(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'locale' => 'en',
        ]);

        $this->actingAs($owner)
            ->get(route('orders.import.create'))
            ->assertOk()
            ->assertSee('Import Orders')
            ->assertSee('Choose an order CSV or XLSX file');

        $template = $this->actingAs($owner)->get(route('orders.import.template'));
        $template->assertOk();
        $this->assertStringContainsString('customer_name,customer_phone,order_number,order_name', $template->streamedContent());
    }

    public function test_order_csv_preview_suggests_supported_columns(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $file = $this->csvFile(
            "Nama Pelanggan,Nomor WhatsApp,Nomor Pesanan,Nama Pesanan,Jumlah,Harga Satuan,Total,Deadline,Status\nBudi,08123456789,WEB-01,Kaos,10,50000,500000,2026-10-30,Baru\n"
        );

        $response = $this->actingAs($owner)->post(route('orders.import.preview'), ['file' => $file]);
        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $this->get($location)
            ->assertOk()
            ->assertSee('WEB-01')
            ->assertSee('<option value="Nama Pelanggan" selected>Nama Pelanggan</option>', false)
            ->assertSee('<option value="Harga Satuan" selected>Harga Satuan</option>', false);
    }

    public function test_order_import_matches_customers_normalizes_values_and_reports_results(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create([
            'user_id' => $owner->id,
            'name' => 'Budi Santoso',
            'phone' => '0812-345-678',
        ]);
        Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'WEB-001',
            'name' => 'Pesanan Lama',
            'quantity' => 1,
            'price_per_unit' => 1000,
            'total_amount' => 1000,
            'status' => 'new',
        ]);

        $file = $this->csvFile(
            "Nama Pelanggan,WhatsApp,Nomor Pesanan,Nama Pesanan,Jumlah,Harga Satuan,Total,Deadline,Status,Deskripsi,Catatan\n".
            "Budi Santoso,+62812345678,WEB-001,Duplikat,1,1000,1000,2026-10-20,Baru,-,-\n".
            "Budi Santoso,+62812345678,WEB-002,Kaos Komunitas,10,75.000,,30/10/2026,Produksi,Cotton hitam,Prioritas\n".
            "Budi Santoso,0812345678,,Spanduk,2,125000,250000,30-11-2026,Selesai,Vinyl,-\n".
            "Tidak Terdaftar,0899999999,WEB-003,Gagal,1,1000,1000,2026-10-20,Baru,-,-\n"
        );

        $preview = $this->actingAs($owner)->post(route('orders.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));
        $response = $this->actingAs($owner)->post(route('orders.import.store'), $this->mapping($token));

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success', fn (string $message) => str_contains($message, '2 pesanan ditambahkan')
            && str_contains($message, '1 duplikat dilewati')
            && str_contains($message, '1 baris gagal'));
        $response->assertSessionHas('order_import_errors');

        $this->assertDatabaseHas('orders', [
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'WEB-002',
            'name' => 'Kaos Komunitas',
            'quantity' => 10,
            'price_per_unit' => 75000,
            'total_amount' => 750000,
            'deadline' => '2026-10-30 00:00:00',
            'status' => 'production',
        ]);
        $this->assertDatabaseHas('orders', [
            'user_id' => $owner->id,
            'order_number' => 'ORD-0001',
            'name' => 'Spanduk',
            'status' => 'completed',
        ]);
        Storage::disk('local')->assertMissing('imports/orders/'.$owner->id.'/'.$token.'.json');
    }

    public function test_imported_custom_order_number_does_not_break_automatic_number_sequence(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Budi']);
        Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'LEGACY-999',
            'name' => 'Legacy',
            'quantity' => 1,
            'price_per_unit' => 0,
            'total_amount' => 0,
            'status' => 'new',
        ]);
        Order::create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-0007',
            'name' => 'Normal',
            'quantity' => 1,
            'price_per_unit' => 0,
            'total_amount' => 0,
            'status' => 'new',
        ]);

        $this->assertSame('ORD-0008', app(OrderNumberService::class)->generate($owner->id));
    }

    public function test_order_import_honors_monthly_plan_quota(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $customer = Customer::create(['user_id' => $owner->id, 'name' => 'Budi']);
        $plan = Plan::create([
            'name' => 'Starter Test',
            'slug' => 'starter-import-test',
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
        $file = $this->csvFile(
            "Nama Pelanggan,Nama Pesanan,Jumlah,Harga Satuan\nBudi,Pesanan Satu,1,1000\nBudi,Pesanan Dua,1,1000\n"
        );

        $preview = $this->actingAs($owner)->post(route('orders.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));
        $mapping = [
            'token' => $token,
            'customer_name_column' => 'Nama Pelanggan',
            'name_column' => 'Nama Pesanan',
            'quantity_column' => 'Jumlah',
            'price_column' => 'Harga Satuan',
        ];

        $response = $this->actingAs($owner)->post(route('orders.import.store'), $mapping);

        $response->assertSessionHas('success', fn (string $message) => str_contains($message, '1 pesanan ditambahkan')
            && str_contains($message, '1 baris gagal'));
        $this->assertSame(1, Order::where('user_id', $owner->id)->count());
        $this->assertSame($customer->id, Order::first()->customer_id);
    }

    public function test_production_operator_cannot_import_orders(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $operator = User::factory()->create([
            'role' => User::ROLE_PRODUCTION,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($operator)
            ->get(route('orders.import.create'))
            ->assertForbidden();
    }

    public function test_order_import_file_is_isolated_between_tenants(): void
    {
        Storage::fake('local');
        $ownerA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $ownerB = User::factory()->create(['role' => User::ROLE_OWNER]);
        $file = $this->csvFile("Nama Pelanggan,Nama Pesanan,Jumlah,Harga Satuan\nBudi,Pesanan,1,1000\n");

        $preview = $this->actingAs($ownerA)->post(route('orders.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));

        $this->actingAs($ownerB)->post(route('orders.import.store'), [
            'token' => $token,
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('orders', 0);
    }

    private function csvFile(string $contents): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('pesanan.csv', $contents);
    }

    private function tokenFromRedirect(string $location): string
    {
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query['token'];
    }

    /** @return array<string, string> */
    private function mapping(string $token): array
    {
        return [
            'token' => $token,
            'customer_name_column' => 'Nama Pelanggan',
            'customer_phone_column' => 'WhatsApp',
            'order_number_column' => 'Nomor Pesanan',
            'name_column' => 'Nama Pesanan',
            'quantity_column' => 'Jumlah',
            'price_column' => 'Harga Satuan',
            'total_column' => 'Total',
            'deadline_column' => 'Deadline',
            'status_column' => 'Status',
            'description_column' => 'Deskripsi',
            'notes_column' => 'Catatan',
        ];
    }
}
