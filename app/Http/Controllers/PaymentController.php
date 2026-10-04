<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private WhatsAppService $whatsAppService) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user->canViewFinances()) {
            abort(403, __('Anda tidak memiliki hak akses untuk melihat data pembayaran.'));
        }

        $storeOwnerId = $user->getStoreOwnerId();

        $query = Payment::whereHas('order', fn ($q) => $q->where('user_id', $storeOwnerId))
            ->with(['order.customer'])
            ->orderByDesc('payment_date');

        if ($search = $request->input('search')) {
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $grossPayments = Payment::whereHas('order', fn ($q) => $q->where('user_id', $storeOwnerId))
            ->where(fn ($q) => $q->where('type', 'payment')->orWhereNull('type'))
            ->sum('amount');

        $totalRefunds = Payment::whereHas('order', fn ($q) => $q->where('user_id', $storeOwnerId))
            ->where('type', 'refund')
            ->sum('amount');

        $totalAmount = $grossPayments - $totalRefunds;

        return view('payments.index', compact('payments', 'totalAmount', 'grossPayments', 'totalRefunds'));
    }

    public function store(Request $request, Order $order)
    {
        $user = auth()->user();
        $this->authorize('update', $order);

        if (! $user->canViewFinances()) {
            abort(403, __('Hanya Kasir / Owner yang dapat mencatat pembayaran.'));
        }

        $validated = $request->validate([
            'type' => 'nullable|in:payment,refund',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,transfer,qris,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $type = $validated['type'] ?? 'payment';
        $validated['type'] = $type;
        $validated['order_id'] = $order->id;

        // Validasi khusus refund
        if ($type === 'refund') {
            if ($order->total_paid <= 0) {
                return back()->withErrors(['amount' => __('Tidak dapat melakukan pengembalian dana karena pesanan ini belum memiliki pembayaran masuk.')])->withInput();
            }

            if ($validated['amount'] > $order->total_paid) {
                return back()->withErrors([
                    'amount' => __('Nominal pengembalian dana (Rp :amount) melebihi total pembayaran yang telah diterima (Maksimal: Rp :maximum).', [
                        'amount' => number_format($validated['amount'], 0, ',', '.'),
                        'maximum' => number_format($order->total_paid, 0, ',', '.'),
                    ]),
                ])->withInput();
            }

            $payment = Payment::create($validated);
            $waUrl = $this->whatsAppService->refundUrl($order, $validated['amount'], $validated['method']);

            return redirect()->route('orders.show', $order)
                ->with('success', __('Pengembalian dana (refund) sebesar Rp :amount berhasil dicatat.', ['amount' => number_format($validated['amount'], 0, ',', '.')]))
                ->with('wa_refund_url', $waUrl)
                ->with('wa_payment_url', $waUrl);
        }

        $payment = Payment::create($validated);
        $waUrl = $this->whatsAppService->paymentUrl($order, $validated['amount']);

        return redirect()->route('orders.show', $order)
            ->with('success', __('Pembayaran berhasil dicatat.'))
            ->with('wa_payment_url', $waUrl);
    }

    public function destroy(Order $order, Payment $payment)
    {
        $user = auth()->user();
        $this->authorize('update', $order);

        if (! $user->canViewFinances()) {
            abort(403, __('Anda tidak memiliki hak akses untuk menghapus pembayaran.'));
        }

        if ($payment->order_id !== $order->id) {
            abort(403);
        }

        $payment->delete();

        return back()->with('success', __('Pembayaran berhasil dihapus.'));
    }
}
