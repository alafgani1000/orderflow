<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Services\TabularImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CustomerImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_import_page_and_template_are_available_in_english(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'locale' => 'en',
        ]);

        $this->actingAs($owner)
            ->get(route('customers.import.create'))
            ->assertOk()
            ->assertSee('Import Customers')
            ->assertSee('Choose a CSV or XLSX file');

        $template = $this->actingAs($owner)->get(route('customers.import.template'));
        $template->assertOk();
        $this->assertStringContainsString('name,phone_number,address,notes', $template->streamedContent());
    }

    public function test_csv_can_be_previewed_with_automatic_column_suggestions(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $file = UploadedFile::fake()->createWithContent(
            'pelanggan.csv',
            "Nama,Nomor WhatsApp,Alamat,Catatan\nBudi,08123456789,Bandung,Pelanggan lama\n"
        );

        $response = $this->actingAs($owner)->post(route('customers.import.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/customers/import?token=', $location);

        $this->get($location)
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('1 baris data')
            ->assertSee('<option value="Nama" selected>Nama</option>', false)
            ->assertSee('<option value="Nomor WhatsApp" selected>Nomor WhatsApp</option>', false);
    }

    public function test_csv_import_creates_valid_customers_and_reports_duplicates_and_failures(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        Customer::create([
            'user_id' => $owner->id,
            'name' => 'Pelanggan Lama',
            'phone' => '0812-345-678',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'pelanggan.csv',
            "Nama,WhatsApp,Alamat,Catatan\nPelanggan Duplikat,0812345678,Jakarta,-\nBudi Baru,0812999000,Bandung,Prioritas\nBudi Salinan,0812999000,Bandung,-\n,0812777000,Surabaya,Tanpa nama\n"
        );

        $preview = $this->actingAs($owner)->post(route('customers.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));

        $response = $this->actingAs($owner)->post(route('customers.import.store'), [
            'token' => $token,
            'name_column' => 'Nama',
            'phone_column' => 'WhatsApp',
            'address_column' => 'Alamat',
            'notes_column' => 'Catatan',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success', fn (string $message) => str_contains($message, '1 pelanggan ditambahkan')
            && str_contains($message, '2 duplikat dilewati')
            && str_contains($message, '1 baris gagal'));
        $response->assertSessionHas('import_errors');

        $this->assertDatabaseHas('customers', [
            'user_id' => $owner->id,
            'name' => 'Budi Baru',
            'phone' => '0812999000',
        ]);
        $this->assertDatabaseCount('customers', 2);
        Storage::disk('local')->assertMissing('imports/customers/'.$owner->id.'/'.$token.'.json');
    }

    public function test_import_file_is_isolated_between_tenants(): void
    {
        Storage::fake('local');
        $ownerA = User::factory()->create(['role' => User::ROLE_OWNER]);
        $ownerB = User::factory()->create(['role' => User::ROLE_OWNER]);
        $file = UploadedFile::fake()->createWithContent(
            'pelanggan.csv',
            "Nama,WhatsApp\nRahasia Tenant A,0812000000\n"
        );

        $preview = $this->actingAs($ownerA)->post(route('customers.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));

        $this->actingAs($ownerB)->post(route('customers.import.store'), [
            'token' => $token,
            'name_column' => 'Nama',
            'phone_column' => 'WhatsApp',
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('customers', ['name' => 'Rahasia Tenant A']);
    }

    public function test_xlsx_reader_supports_the_first_worksheet(): void
    {
        Storage::fake('local');
        $reader = app(TabularImportService::class);
        $rows = [
            ['Name', 'Phone Number', 'Address'],
            ['Alex Smith', '08123456789', '10 Main Street'],
        ];
        $file = $this->makeXlsx($rows);

        $data = $reader->read($file);

        $this->assertSame(['Name', 'Phone Number', 'Address'], $data['headers']);
        $this->assertSame('Alex Smith', $data['rows'][0]['Name']);
        $this->assertSame('08123456789', $data['rows'][0]['Phone Number']);

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $response = $this->actingAs($owner)->post(route('customers.import.preview'), [
            'file' => $this->makeXlsx($rows),
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_a_column_cannot_be_mapped_to_multiple_customer_fields(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $file = UploadedFile::fake()->createWithContent(
            'pelanggan.csv',
            "Nama,WhatsApp\nBudi,0812000000\n"
        );
        $preview = $this->actingAs($owner)->post(route('customers.import.preview'), ['file' => $file]);
        $token = $this->tokenFromRedirect($preview->headers->get('Location'));

        $this->actingAs($owner)->post(route('customers.import.store'), [
            'token' => $token,
            'name_column' => 'Nama',
            'phone_column' => 'Nama',
        ])->assertSessionHasErrors('columns');

        $this->assertDatabaseCount('customers', 0);
    }

    private function tokenFromRedirect(string $location): string
    {
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query['token'];
    }

    /** @param array<int, array<int, string>> $rows */
    private function makeXlsx(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'orderflow-xlsx-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Customers" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');

        $xmlRows = '';
        foreach ($rows as $rowIndex => $row) {
            $cells = '';
            foreach ($row as $columnIndex => $value) {
                $reference = chr(65 + $columnIndex).($rowIndex + 1);
                $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cells .= '<c r="'.$reference.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
            }
            $xmlRows .= '<row r="'.($rowIndex + 1).'">'.$cells.'</row>';
        }

        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$xmlRows.'</sheetData></worksheet>');
        $zip->close();

        return new UploadedFile(
            $path,
            'customers.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
