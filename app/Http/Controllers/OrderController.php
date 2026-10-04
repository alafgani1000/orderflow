<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderNumberService;
use App\Services\SecureFileStorage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private OrderNumberService $orderNumberService,
        private WhatsAppService $whatsAppService,
        private SecureFileStorage $secureFiles,
    ) {}

    public function index(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $query = Order::where('user_id', $storeOwnerId)->with('customer');

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter deadline
        if ($deadline = $request->input('deadline')) {
            match ($deadline) {
                'today' => $query->dueToday(),
                'overdue' => $query->overdue(),
                'week' => $query->whereNotNull('deadline')
                    ->where('deadline', '<=', now()->addDays(7)->toDateString())
                    ->active(),
                default => null,
            };
        }

        // Filter pembayaran
        if ($request->input('payment') === 'unpaid') {
            $query->unpaid();
        }

        // Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statuses = Order::STATUSES;

        return view('orders.index', compact('orders', 'statuses'));
    }

    public function kanban(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $query = Order::where('user_id', $storeOwnerId)
            ->where('status', '!=', 'cancelled')
            ->with(['customer']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->get();

        $pipeline = [
            'new' => '1. Baru',
            'waiting_design' => '2. Menunggu Desain',
            'design_approved' => '3. Desain Disetujui',
            'production' => '4. Sedang Produksi',
            'completed' => '5. Selesai',
            'delivered' => '6. Sudah Diambil',
        ];

        $columns = [];
        foreach ($pipeline as $statusKey => $label) {
            $columns[$statusKey] = [
                'label' => $label,
                'orders' => $orders->where('status', $statusKey)->values(),
            ];
        }

        return view('orders.kanban', compact('columns', 'pipeline'));
    }

    public function create()
    {
        if (! auth()->user()->canCreateOrder()) {
            return redirect()->route('orders.index')
                ->with('error', __('Batas kuota pesanan bulanan paket Anda telah tercapai. Silakan upgrade paket langganan untuk menambah pesanan.'));
        }

        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $customers = Customer::where('user_id', $storeOwnerId)->orderBy('name')->get();
        $statuses = Order::STATUSES;

        return view('orders.create', compact('customers', 'statuses'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->canCreateOrder()) {
            return redirect()->route('orders.index')
                ->with('error', __('Batas kuota pesanan bulanan paket Anda telah tercapai. Silakan upgrade paket langganan untuk menambah pesanan.'));
        }

        $storeOwnerId = auth()->user()->getStoreOwnerId();

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('user_id', $storeOwnerId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'status' => 'required|in:'.implode(',', array_keys(Order::STATUSES)),
            'notes' => 'nullable|string',
            'size_breakdown' => 'nullable|array',
            // DP (pembayaran awal)
            'dp_amount' => 'nullable|numeric|min:0',
            'dp_method' => 'nullable|in:cash,transfer,qris,other',
        ]);

        $validated['user_id'] = $storeOwnerId;
        $validated['order_number'] = $this->orderNumberService->generate($storeOwnerId);

        // Bersihkan size_breakdown: hanya simpan yang nilainya > 0
        if (! empty($validated['size_breakdown'])) {
            $cleanSizes = [];
            foreach ($validated['size_breakdown'] as $sizeName => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && ! empty(trim($sizeName))) {
                    $cleanSizes[trim($sizeName)] = $qty;
                }
            }
            $validated['size_breakdown'] = ! empty($cleanSizes) ? $cleanSizes : null;
        }

        $order = Order::create($validated);

        // Simpan DP jika ada
        if (! empty($validated['dp_amount']) && $validated['dp_amount'] > 0) {
            $order->payments()->create([
                'amount' => $validated['dp_amount'],
                'payment_date' => now()->toDateString(),
                'method' => $validated['dp_method'] ?? 'cash',
                'notes' => 'DP / Uang Muka',
            ]);
        }

        // Upload file desain jika ada
        if ($request->hasFile('design_files')) {
            foreach ($request->file('design_files') as $file) {
                $order->files()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $this->secureFiles->store($file, "designs/{$order->id}"),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $waStatusUrl = $this->whatsAppService->statusUrl($order);

        return redirect()->route('orders.show', $order)
            ->with('success', __('Pesanan #:number (:name) berhasil dibuat.', ['number' => $order->order_number, 'name' => $order->name]))
            ->with('wa_status_url', $waStatusUrl);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['customer', 'payments', 'files']);

        $waStatusUrl = $this->whatsAppService->statusUrl($order);
        $waCompletedUrl = $this->whatsAppService->completedUrl($order);
        $waReminderUrl = $this->whatsAppService->reminderUrl($order);
        $waStatusText = $this->whatsAppService->statusMessage($order);
        $waReminderText = $this->whatsAppService->reminderMessage($order);
        $waCompletedText = $this->whatsAppService->completedMessage($order);

        return view('orders.show', compact(
            'order',
            'waStatusUrl',
            'waCompletedUrl',
            'waReminderUrl',
            'waStatusText',
            'waReminderText',
            'waCompletedText'
        ));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $customers = Customer::where('user_id', $storeOwnerId)->orderBy('name')->get();
        $statuses = Order::STATUSES;

        return view('orders.edit', compact('order', 'customers', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $storeOwnerId = auth()->user()->getStoreOwnerId();

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('user_id', $storeOwnerId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date',
            'status' => 'required|in:'.implode(',', array_keys(Order::STATUSES)),
            'notes' => 'nullable|string',
            'size_breakdown' => 'nullable|array',
        ]);

        // Proteksi integritas keuangan: jika staf tidak memiliki hak akses finansial, abaikan perubahan nominal harga
        if (! auth()->user()->canViewFinances()) {
            unset($validated['price_per_unit'], $validated['total_amount']);
        }

        // Bersihkan size_breakdown
        if (isset($validated['size_breakdown'])) {
            $cleanSizes = [];
            foreach ($validated['size_breakdown'] as $sizeName => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && ! empty(trim($sizeName))) {
                    $cleanSizes[trim($sizeName)] = $qty;
                }
            }
            $validated['size_breakdown'] = ! empty($cleanSizes) ? $cleanSizes : null;
        }

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', __('Pesanan berhasil diperbarui.'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Order::STATUSES)),
        ]);

        $order->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'status_label' => __(Order::STATUSES[$order->status]),
                'message' => __('Status berubah menjadi: :status', ['status' => __(Order::STATUSES[$validated['status']])]),
            ]);
        }

        return back()->with('success', __('Status berubah menjadi: :status', ['status' => __(Order::STATUSES[$validated['status']])]));
    }

    public function invoice(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['customer', 'payments', 'user']);

        return view('orders.invoice', compact('order'));
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        // Hapus file desain dari storage
        foreach ($order->files as $file) {
            $this->secureFiles->delete($file->file_path);
        }

        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', __('Pesanan berhasil dihapus.'));
    }
}
