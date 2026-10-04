<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\OrderNumberService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsAppService,
        private OrderNumberService $orderNumberService,
    ) {}

    public function index(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $query = Quotation::where('user_id', $storeOwnerId)->with('customer');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $quotations = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total'     => Quotation::where('user_id', $storeOwnerId)->count(),
            'draft'     => Quotation::where('user_id', $storeOwnerId)->where('status', Quotation::STATUS_DRAFT)->count(),
            'sent'      => Quotation::where('user_id', $storeOwnerId)->where('status', Quotation::STATUS_SENT)->count(),
            'approved'  => Quotation::where('user_id', $storeOwnerId)->where('status', Quotation::STATUS_APPROVED)->count(),
            'converted' => Quotation::where('user_id', $storeOwnerId)->where('status', Quotation::STATUS_CONVERTED)->count(),
        ];

        return view('quotations.index', compact('quotations', 'stats'));
    }

    public function create(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $customers = Customer::where('user_id', $storeOwnerId)->orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');

        return view('quotations.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();

        $validated = $request->validate([
            'customer_id'   => ['required', Rule::exists('customers', 'id')->where('user_id', $storeOwnerId)],
            'title'         => 'required|string|max:255',
            'valid_until'   => 'nullable|date',
            'discount'      => 'nullable|numeric|min:0',
            'tax'           => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'   => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $storeOwnerId) {
            $quotationNumber = Quotation::generateQuotationNumber($storeOwnerId);

            $quotation = Quotation::create([
                'user_id'          => $storeOwnerId,
                'customer_id'      => $validated['customer_id'],
                'quotation_number' => $quotationNumber,
                'title'            => $validated['title'],
                'status'           => Quotation::STATUS_DRAFT,
                'valid_until'      => $validated['valid_until'] ?? now()->addDays(14)->toDateString(),
                'discount'         => $validated['discount'] ?? 0,
                'tax'              => $validated['tax'] ?? 0,
                'notes'            => $validated['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $index => $itemData) {
                $totalPrice = (int) $itemData['quantity'] * (float) $itemData['unit_price'];
                $subtotal += $totalPrice;

                $quotation->items()->create([
                    'item_name'   => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity'    => $itemData['quantity'],
                    'unit_price'  => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'sort_order'  => $index,
                ]);
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $tax = (float) ($validated['tax'] ?? 0);
            $totalAmount = max(0, ($subtotal - $discount) + $tax);

            $quotation->update([
                'subtotal'     => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            $waUrl = $this->whatsAppService->quotationUrl($quotation);

            return redirect()->route('quotations.show', $quotation)
                ->with('success', __('Penawaran #:number berhasil dibuat.', ['number' => $quotation->quotation_number]))
                ->with('wa_quotation_url', $waUrl);
        });
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);
        $quotation->load(['customer', 'items', 'order']);

        $waUrl = $this->whatsAppService->quotationUrl($quotation);
        $waMessage = $this->whatsAppService->quotationMessage($quotation);

        return view('quotations.show', compact('quotation', 'waUrl', 'waMessage'));
    }

    public function edit(Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        if ($quotation->isConverted()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', __('Penawaran yang sudah dikonversi menjadi pesanan tidak dapat diubah lagi.'));
        }

        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $customers = Customer::where('user_id', $storeOwnerId)->orderBy('name')->get();
        $quotation->load('items');

        return view('quotations.edit', compact('quotation', 'customers'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        if ($quotation->isConverted()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', __('Penawaran yang sudah dikonversi menjadi pesanan tidak dapat diubah lagi.'));
        }

        $storeOwnerId = auth()->user()->getStoreOwnerId();

        $validated = $request->validate([
            'customer_id'   => ['required', Rule::exists('customers', 'id')->where('user_id', $storeOwnerId)],
            'title'         => 'required|string|max:255',
            'status'        => 'required|in:' . implode(',', array_keys(Quotation::STATUSES)),
            'valid_until'   => 'nullable|date',
            'discount'      => 'nullable|numeric|min:0',
            'tax'           => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'   => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $quotation) {
            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'title'       => $validated['title'],
                'status'      => $validated['status'],
                'valid_until' => $validated['valid_until'],
                'discount'    => $validated['discount'] ?? 0,
                'tax'         => $validated['tax'] ?? 0,
                'notes'       => $validated['notes'] ?? null,
            ]);

            // Recreate items
            $quotation->items()->delete();

            $subtotal = 0;
            foreach ($validated['items'] as $index => $itemData) {
                $totalPrice = (int) $itemData['quantity'] * (float) $itemData['unit_price'];
                $subtotal += $totalPrice;

                $quotation->items()->create([
                    'item_name'   => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity'    => $itemData['quantity'],
                    'unit_price'  => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'sort_order'  => $index,
                ]);
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $tax = (float) ($validated['tax'] ?? 0);
            $totalAmount = max(0, ($subtotal - $discount) + $tax);

            $quotation->update([
                'subtotal'     => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            return redirect()->route('quotations.show', $quotation)
                ->with('success', __('Penawaran berhasil diperbarui.'));
        });
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', __('Penawaran berhasil dihapus.'));
    }

    /**
     * Konversi penawaran menjadi pesanan (Order).
     */
    public function convertToOrder(Quotation $quotation)
    {
        $this->authorize('convert', $quotation);

        $user = auth()->user();

        // Periksa kuota pembuatan pesanan paket SaaS
        if (! $user->canCreateOrder()) {
            return back()->with('error', __('Batas kuota pesanan bulanan untuk paket Anda telah tercapai. Silakan upgrade paket untuk melanjutkan.'));
        }

        $storeOwnerId = $user->getStoreOwnerId();

        return DB::transaction(function () use ($quotation, $storeOwnerId) {
            $totalQuantity = (int) $quotation->items()->sum('quantity');
            if ($totalQuantity <= 0) {
                $totalQuantity = 1;
            }

            $orderNumber = $this->orderNumberService->generate($storeOwnerId);

            // Rangkum item penawaran ke catatan pesanan
            $itemSummaries = $quotation->items->map(function ($item) {
                return "- {$item->item_name} ({$item->quantity} pcs @ Rp" . number_format($item->unit_price, 0, ',', '.') . ")" . ($item->description ? ": {$item->description}" : '');
            })->join("\n");

            $combinedNotes = trim("Konversi dari Penawaran #{$quotation->quotation_number}\n\n" . ($quotation->notes ? "Catatan Penawaran:\n{$quotation->notes}\n\n" : '') . "Rincian Item:\n{$itemSummaries}");

            $order = Order::create([
                'user_id'        => $storeOwnerId,
                'customer_id'    => $quotation->customer_id,
                'order_number'   => $orderNumber,
                'name'           => $quotation->title,
                'description'    => $itemSummaries,
                'quantity'       => $totalQuantity,
                'price_per_unit' => round($quotation->total_amount / $totalQuantity, 2),
                'total_amount'   => $quotation->total_amount,
                'deadline'       => now()->addDays(7)->toDateString(),
                'status'         => 'new',
                'notes'          => $combinedNotes,
            ]);

            $quotation->update([
                'status'   => Quotation::STATUS_CONVERTED,
                'order_id' => $order->id,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', __('Penawaran #:quo berhasil dikonversi menjadi Pesanan #:ord!', [
                    'quo' => $quotation->quotation_number,
                    'ord' => $order->order_number,
                ]));
        });
    }

    /**
     * Tandai penawaran sebagai telah dikirim ke pelanggan.
     */
    public function markAsSent(Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        if ($quotation->status === Quotation::STATUS_DRAFT) {
            $quotation->update(['status' => Quotation::STATUS_SENT]);
        }

        return back()->with('success', __('Status penawaran diperbarui menjadi Terkirim.'));
    }
}
