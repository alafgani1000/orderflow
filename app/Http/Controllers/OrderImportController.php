<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderNumberService;
use App\Services\TabularImportService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderImportController extends Controller
{
    public function __construct(private OrderNumberService $orderNumberService) {}

    public function create(Request $request): View
    {
        $this->authorizeImport();
        $this->cleanupExpiredImports();
        $token = (string) $request->query('token');

        if (! Str::isUuid($token)) {
            return view('orders.import');
        }

        $path = $this->importPath($token);
        if (! Storage::disk('local')->exists($path)) {
            return view('orders.import');
        }

        $data = json_decode(Storage::disk('local')->get($path), true, 512, JSON_THROW_ON_ERROR);

        return view('orders.import', [
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
        $this->authorizeImport();

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");

            if (app()->getLocale() === 'en') {
                fputcsv($output, ['customer_name', 'customer_phone', 'order_number', 'order_name', 'quantity', 'unit_price', 'total', 'deadline', 'status', 'description', 'notes']);
                fputcsv($output, ['Alex Smith', '081234567890', 'WEB-001', 'Community T-Shirts', '50', '75000', '3750000', '2026-10-30', 'new', 'Black cotton, front print', 'Deliver in the afternoon']);
            } else {
                fputcsv($output, ['nama_pelanggan', 'nomor_whatsapp', 'nomor_pesanan', 'nama_pesanan', 'jumlah', 'harga_satuan', 'total', 'deadline', 'status', 'deskripsi', 'catatan']);
                fputcsv($output, ['Budi Santoso', '081234567890', 'WEB-001', 'Kaos Komunitas', '50', '75000', '3750000', '2026-10-30', 'baru', 'Cotton hitam, sablon depan', 'Kirim sore hari']);
            }

            fclose($output);
        }, 'template-import-pesanan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function preview(Request $request, TabularImportService $reader): RedirectResponse
    {
        $this->authorizeImport();
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
        Storage::disk('local')->put($this->importPath($token), json_encode([
            ...$data,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'created_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return redirect()->route('orders.import.create', ['token' => $token]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeImport();
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
        $validated = $request->validate($this->mappingRules($headers));
        $selectedColumns = array_values(array_filter($validated, fn ($value, $key) => $key !== 'token' && filled($value), ARRAY_FILTER_USE_BOTH));

        if (count($selectedColumns) !== count(array_unique($selectedColumns))) {
            return back()->withErrors(['columns' => __('Satu kolom file tidak boleh dipakai untuk lebih dari satu data pesanan.')]);
        }

        $user = auth()->user();
        $ownerId = $user->getStoreOwnerId();
        $customers = Customer::where('user_id', $ownerId)->get();
        $customersByPhone = $customers
            ->filter(fn (Customer $customer) => $this->normalizePhone($customer->phone) !== '')
            ->keyBy(fn (Customer $customer) => $this->normalizePhone($customer->phone));
        $customersByName = $customers->groupBy(fn (Customer $customer) => $this->normalizeText($customer->name));
        $knownOrderNumbers = Order::where('user_id', $ownerId)
            ->pluck('order_number')
            ->mapWithKeys(fn (string $number) => [mb_strtoupper(trim($number)) => true])
            ->all();

        $imported = 0;
        $duplicates = 0;
        $failed = 0;
        $errors = [];

        foreach ($data['rows'] ?? [] as $index => $row) {
            $rowNumber = $index + 2;
            $customer = $this->resolveCustomer($row, $validated, $customersByPhone, $customersByName);

            if (! $customer) {
                $failed++;
                $this->addError($errors, __('Baris :row: pelanggan tidak ditemukan atau namanya tidak unik.', ['row' => $rowNumber]));

                continue;
            }

            $quantity = $this->parseNumber($this->mappedValue($row, $validated['quantity_column']));
            $unitPrice = $this->parseNumber($this->mappedValue($row, $validated['price_column']));
            $mappedTotal = $this->mappedValue($row, $validated['total_column'] ?? null);
            $total = $mappedTotal !== null ? $this->parseNumber($mappedTotal) : null;
            $rawStatus = $this->mappedValue($row, $validated['status_column'] ?? null);
            $rawDeadline = $this->mappedValue($row, $validated['deadline_column'] ?? null);
            $orderNumber = $this->mappedValue($row, $validated['order_number_column'] ?? null);
            $orderNumber = $orderNumber !== null ? trim($orderNumber) : null;

            $attributes = [
                'name' => $this->mappedValue($row, $validated['name_column']),
                'description' => $this->mappedValue($row, $validated['description_column'] ?? null),
                'quantity' => $quantity,
                'price_per_unit' => $unitPrice,
                'total_amount' => $total ?? (($quantity !== null && $unitPrice !== null) ? $quantity * $unitPrice : null),
                'deadline' => $rawDeadline !== null ? $this->parseDate($rawDeadline) : null,
                'status' => $this->normalizeStatus($rawStatus),
                'notes' => $this->mappedValue($row, $validated['notes_column'] ?? null),
                'order_number' => $orderNumber,
            ];

            $validator = Validator::make($attributes, [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
                'price_per_unit' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
                'total_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
                'deadline' => ['nullable', 'date_format:Y-m-d'],
                'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
                'notes' => ['nullable', 'string', 'max:5000'],
                'order_number' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $failed++;
                $this->addError($errors, __('Baris :row: :error', [
                    'row' => $rowNumber,
                    'error' => $validator->errors()->first(),
                ]));

                continue;
            }

            $numberKey = $orderNumber !== null ? mb_strtoupper($orderNumber) : null;
            if ($numberKey !== null && isset($knownOrderNumbers[$numberKey])) {
                $duplicates++;

                continue;
            }

            if (! $user->canCreateOrder()) {
                $failed++;
                $this->addError($errors, __('Baris :row: batas kuota pesanan bulanan telah tercapai.', ['row' => $rowNumber]));

                continue;
            }

            $validOrder = $validator->validated();
            $validOrder['order_number'] = $orderNumber ?: $this->orderNumberService->generate($ownerId);

            try {
                Order::create([
                    ...$validOrder,
                    'user_id' => $ownerId,
                    'customer_id' => $customer->id,
                ]);
            } catch (QueryException) {
                $failed++;
                $this->addError($errors, __('Baris :row: nomor pesanan sudah digunakan.', ['row' => $rowNumber]));

                continue;
            }

            $knownOrderNumbers[mb_strtoupper($validOrder['order_number'])] = true;
            $imported++;
        }

        Storage::disk('local')->delete($path);

        return redirect()->route('orders.index')->with([
            'success' => __('Import selesai: :imported pesanan ditambahkan, :duplicates duplikat dilewati, dan :failed baris gagal.', [
                'imported' => $imported,
                'duplicates' => $duplicates,
                'failed' => $failed,
            ]),
            'order_import_errors' => $errors,
        ]);
    }

    /** @param array<int, string> $headers */
    private function mappingRules(array $headers): array
    {
        $column = fn (bool $required = false): array => [$required ? 'required' : 'nullable', Rule::in($headers)];

        return [
            'token' => ['required', 'uuid'],
            'customer_name_column' => $column(true),
            'customer_phone_column' => $column(),
            'order_number_column' => $column(),
            'name_column' => $column(true),
            'quantity_column' => $column(true),
            'price_column' => $column(true),
            'total_column' => $column(),
            'deadline_column' => $column(),
            'status_column' => $column(),
            'description_column' => $column(),
            'notes_column' => $column(),
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<string, string|null>  $mapping
     * @param  Collection<string, Customer>  $customersByPhone
     * @param  Collection<string, Collection<int, Customer>>  $customersByName
     */
    private function resolveCustomer(array $row, array $mapping, Collection $customersByPhone, Collection $customersByName): ?Customer
    {
        $phone = $this->normalizePhone($this->mappedValue($row, $mapping['customer_phone_column'] ?? null));
        if ($phone !== '' && $customersByPhone->has($phone)) {
            return $customersByPhone->get($phone);
        }

        $name = $this->normalizeText($this->mappedValue($row, $mapping['customer_name_column']) ?? '');
        $matches = $customersByName->get($name, collect());

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** @param array<int, string> $headers */
    private function suggestColumns(array $headers): array
    {
        $aliases = [
            'customer_name' => ['nama pelanggan', 'customer name', 'customer', 'pelanggan'],
            'customer_phone' => ['nomor whatsapp', 'customer phone', 'phone number', 'whatsapp', 'wa'],
            'order_number' => ['nomor pesanan', 'order number', 'no order', 'order id'],
            'name' => ['nama pesanan', 'order name', 'nama pekerjaan', 'item', 'pekerjaan'],
            'quantity' => ['jumlah', 'quantity', 'qty', 'jumlah pcs'],
            'price' => ['harga satuan', 'unit price', 'price per unit', 'harga'],
            'total' => ['total', 'total biaya', 'total amount'],
            'deadline' => ['deadline', 'target selesai', 'due date'],
            'status' => ['status', 'status pesanan'],
            'description' => ['deskripsi', 'description', 'spesifikasi'],
            'notes' => ['catatan', 'notes', 'note', 'keterangan'],
        ];

        return collect($aliases)->mapWithKeys(function (array $fieldAliases, string $field) use ($headers): array {
            return [$field => collect($headers)->first(
                fn (string $header) => in_array($this->normalizeHeader($header), $fieldAliases, true)
            )];
        })->all();
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

    private function parseNumber(?string $value): int|float|null
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', trim($value)) ?? '';
        if ($clean === '' || in_array($clean, ['-', '.', ','], true)) {
            return null;
        }

        $lastComma = strrpos($clean, ',');
        $lastDot = strrpos($clean, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            $clean = str_replace($thousandsSeparator, '', $clean);
            $clean = str_replace($decimalSeparator, '.', $clean);
        } elseif ($lastComma !== false || $lastDot !== false) {
            $separator = $lastComma !== false ? ',' : '.';
            $position = $lastComma !== false ? $lastComma : $lastDot;
            $decimalDigits = strlen($clean) - $position - 1;
            $clean = $decimalDigits === 3
                ? str_replace($separator, '', $clean)
                : str_replace($separator, '.', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseDate(string $value): string
    {
        $value = trim($value);

        if (is_numeric($value) && (float) $value > 1) {
            return Carbon::create(1899, 12, 30)->addDays((int) floor((float) $value))->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Try the next supported format.
            }
        }

        return $value;
    }

    private function normalizeStatus(?string $status): string
    {
        if ($status === null || trim($status) === '') {
            return 'new';
        }

        $normalized = $this->normalizeText(str_replace(['-', '_'], ' ', $status));
        $aliases = [
            'new' => 'new',
            'baru' => 'new',
            'waiting design' => 'waiting_design',
            'menunggu desain' => 'waiting_design',
            'design approved' => 'design_approved',
            'desain disetujui' => 'design_approved',
            'production' => 'production',
            'produksi' => 'production',
            'sedang produksi' => 'production',
            'completed' => 'completed',
            'selesai' => 'completed',
            'delivered' => 'delivered',
            'dikirim' => 'delivered',
            'diambil' => 'delivered',
            'sudah diambil' => 'delivered',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'dibatalkan' => 'cancelled',
        ];

        return $aliases[$normalized] ?? '__invalid__';
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return str_starts_with($digits, '8') ? '62'.$digits : $digits;
    }

    private function normalizeText(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }

    private function normalizeHeader(string $header): string
    {
        return $this->normalizeText(str_replace(['-', '_'], ' ', $header));
    }

    /** @param array<int, string> $errors */
    private function addError(array &$errors, string $message): void
    {
        if (count($errors) < 10) {
            $errors[] = $message;
        }
    }

    private function importPath(string $token): string
    {
        return 'imports/orders/'.auth()->user()->getStoreOwnerId().'/'.$token.'.json';
    }

    private function cleanupExpiredImports(): void
    {
        $disk = Storage::disk('local');
        $directory = 'imports/orders/'.auth()->user()->getStoreOwnerId();
        $expiresBefore = now()->subHours(2)->timestamp;

        foreach ($disk->files($directory) as $file) {
            if ($disk->lastModified($file) < $expiresBefore) {
                $disk->delete($file);
            }
        }
    }

    private function authorizeImport(): void
    {
        abort_if(auth()->user()->isProduction(), 403, __('Operator produksi tidak dapat mengimpor pesanan.'));
    }
}
