<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\TabularImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerImportController extends Controller
{
    public function create(Request $request): View
    {
        $this->cleanupExpiredImports();
        $token = (string) $request->query('token');

        if (! Str::isUuid($token)) {
            return view('customers.import');
        }

        $path = $this->importPath($token);
        if (! Storage::disk('local')->exists($path)) {
            return view('customers.import');
        }

        $data = json_decode(Storage::disk('local')->get($path), true, 512, JSON_THROW_ON_ERROR);

        return view('customers.import', [
            'token' => $token,
            'headers' => $data['headers'],
            'previewRows' => array_slice($data['rows'], 0, 10),
            'totalRows' => count($data['rows']),
            'suggested' => $this->suggestColumns($data['headers']),
            'fileName' => $data['file_name'] ?? __('File spreadsheet'),
        ]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, app()->getLocale() === 'en'
                ? ['name', 'phone_number', 'address', 'notes']
                : ['nama', 'nomor_whatsapp', 'alamat', 'catatan']);
            fputcsv($output, app()->getLocale() === 'en'
                ? ['Alex Smith', '081234567890', '10 Main Street', 'Community customer']
                : ['Budi Santoso', '081234567890', 'Jl. Merdeka No. 10', 'Pelanggan komunitas']);
            fclose($output);
        }, 'template-import-pelanggan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function preview(Request $request, TabularImportService $reader): View|RedirectResponse
    {
        $this->cleanupExpiredImports();
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt,xlsx'],
        ]);

        try {
            $data = $reader->read($request->file('file'));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        $token = (string) Str::uuid();
        $path = $this->importPath($token);
        Storage::disk('local')->put($path, json_encode([
            ...$data,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'created_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return redirect()->route('customers.import.create', ['token' => $token]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->cleanupExpiredImports();
        $token = (string) $request->input('token');

        if (! Str::isUuid($token)) {
            return back()->withErrors(['file' => __('Sesi import tidak valid. Unggah kembali file Anda.')]);
        }

        $path = $this->importPath($token);
        if (! Storage::disk('local')->exists($path)) {
            return back()->withErrors(['file' => __('Sesi import telah berakhir. Unggah kembali file Anda.')]);
        }

        $data = json_decode(Storage::disk('local')->get($path), true, 512, JSON_THROW_ON_ERROR);
        $headers = $data['headers'] ?? [];

        $validated = $request->validate([
            'token' => ['required', 'uuid'],
            'name_column' => ['required', Rule::in($headers)],
            'phone_column' => ['nullable', Rule::in($headers)],
            'address_column' => ['nullable', Rule::in($headers)],
            'notes_column' => ['nullable', Rule::in($headers)],
        ]);

        $selectedColumns = array_values(array_filter([
            $validated['name_column'],
            $validated['phone_column'] ?? null,
            $validated['address_column'] ?? null,
            $validated['notes_column'] ?? null,
        ]));

        if (count($selectedColumns) !== count(array_unique($selectedColumns))) {
            return back()->withErrors(['columns' => __('Satu kolom file tidak boleh dipakai untuk lebih dari satu data pelanggan.')]);
        }

        $ownerId = auth()->user()->getStoreOwnerId();
        $existingCustomers = Customer::where('user_id', $ownerId)->get(['name', 'phone']);
        $knownPhones = $existingCustomers->mapWithKeys(function (Customer $customer): array {
            $phone = $this->normalizePhone($customer->phone);

            return $phone !== '' ? [$phone => true] : [];
        })->all();
        $knownNamesWithoutPhone = $existingCustomers
            ->filter(fn (Customer $customer) => $this->normalizePhone($customer->phone) === '')
            ->mapWithKeys(fn (Customer $customer) => [$this->normalizeName($customer->name) => true])
            ->all();

        $imported = 0;
        $duplicates = 0;
        $failed = 0;
        $errors = [];

        foreach ($data['rows'] ?? [] as $index => $row) {
            $attributes = [
                'name' => trim((string) ($row[$validated['name_column']] ?? '')),
                'phone' => $this->mappedValue($row, $validated['phone_column'] ?? null),
                'address' => $this->mappedValue($row, $validated['address_column'] ?? null),
                'notes' => $this->mappedValue($row, $validated['notes_column'] ?? null),
            ];

            $validator = Validator::make($attributes, [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:2000'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);

            if ($validator->fails()) {
                $failed++;
                if (count($errors) < 10) {
                    $errors[] = __('Baris :row: :error', [
                        'row' => $index + 2,
                        'error' => $validator->errors()->first(),
                    ]);
                }

                continue;
            }

            $phoneKey = $this->normalizePhone($attributes['phone']);
            $nameKey = $this->normalizeName($attributes['name']);
            $isDuplicate = $phoneKey !== ''
                ? isset($knownPhones[$phoneKey])
                : isset($knownNamesWithoutPhone[$nameKey]);

            if ($isDuplicate) {
                $duplicates++;

                continue;
            }

            Customer::create([
                ...$validator->validated(),
                'user_id' => $ownerId,
            ]);

            $imported++;
            if ($phoneKey !== '') {
                $knownPhones[$phoneKey] = true;
            } else {
                $knownNamesWithoutPhone[$nameKey] = true;
            }
        }

        Storage::disk('local')->delete($path);

        return redirect()->route('customers.index')->with([
            'success' => __('Import selesai: :imported pelanggan ditambahkan, :duplicates duplikat dilewati, dan :failed baris gagal.', [
                'imported' => $imported,
                'duplicates' => $duplicates,
                'failed' => $failed,
            ]),
            'import_errors' => $errors,
        ]);
    }

    private function importPath(string $token): string
    {
        return 'imports/customers/'.auth()->user()->getStoreOwnerId().'/'.$token.'.json';
    }

    private function cleanupExpiredImports(): void
    {
        $disk = Storage::disk('local');
        $directory = 'imports/customers/'.auth()->user()->getStoreOwnerId();
        $expiresBefore = now()->subHours(2)->timestamp;

        foreach ($disk->files($directory) as $file) {
            if ($disk->lastModified($file) < $expiresBefore) {
                $disk->delete($file);
            }
        }
    }

    /**
     * @param  array<int, string>  $headers
     * @return array{name: ?string, phone: ?string, address: ?string, notes: ?string}
     */
    private function suggestColumns(array $headers): array
    {
        $aliases = [
            'name' => ['nama', 'nama pelanggan', 'customer', 'customer name', 'name'],
            'phone' => ['nomor whatsapp', 'nomor_whatsapp', 'whatsapp', 'wa', 'phone', 'phone number', 'nomor telepon', 'no hp'],
            'address' => ['alamat', 'address'],
            'notes' => ['catatan', 'notes', 'note', 'keterangan'],
        ];

        $suggested = [];
        foreach ($aliases as $field => $fieldAliases) {
            $suggested[$field] = collect($headers)->first(
                fn (string $header) => in_array($this->normalizeHeader($header), $fieldAliases, true)
            );
        }

        return $suggested;
    }

    /** @param array<string, string> $row */
    private function mappedValue(array $row, ?string $column): ?string
    {
        if (! $column) {
            return null;
        }

        $value = trim((string) ($row[$column] ?? ''));

        return $value !== '' ? $value : null;
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return str_starts_with($digits, '8') ? '62'.$digits : $digits;
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }

    private function normalizeHeader(string $header): string
    {
        return mb_strtolower(trim(str_replace(['-', '_'], ' ', $header)));
    }
}
