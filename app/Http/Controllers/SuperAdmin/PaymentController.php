<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionInvoice::with(['user', 'plan', 'subscription']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('invoices'));
    }

    public function approve(SubscriptionInvoice $invoice)
    {
        if ($invoice->status === SubscriptionInvoice::STATUS_PAID) {
            return back()->with('error', 'Tagihan ini sudah disetujui sebelumnya.');
        }

        $invoice->update([
            'status'  => SubscriptionInvoice::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $user = $invoice->user;
        $plan = $invoice->plan;

        $duration = $plan->billing_period === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription = $user->subscription;
        if (!$subscription) {
            Subscription::create([
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'status'         => Subscription::STATUS_ACTIVE,
                'starts_at'      => now(),
                'ends_at'        => $duration,
                'payment_method' => $invoice->payment_method,
            ]);
        } else {
            // Jika subscription masih aktif, perpanjang dari ends_at yang ada
            $baseDate = ($subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                ? $subscription->ends_at
                : now();

            $newEndsAt = $plan->billing_period === 'yearly'
                ? (clone $baseDate)->addYear()
                : (clone $baseDate)->addMonth();

            $subscription->update([
                'plan_id'        => $plan->id,
                'status'         => Subscription::STATUS_ACTIVE,
                'ends_at'        => $newEndsAt,
                'payment_method' => $invoice->payment_method,
            ]);
        }

        return back()->with('success', "Pembayaran invoice #{$invoice->invoice_number} berhasil disetujui. Paket {$plan->name} telah aktif.");
    }

    public function reject(Request $request, SubscriptionInvoice $invoice)
    {
        $validated = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        $invoice->update([
            'status' => SubscriptionInvoice::STATUS_REJECTED,
            'notes'  => 'Ditolak: ' . $validated['rejection_note'],
        ]);

        return back()->with('success', "Pembayaran invoice #{$invoice->invoice_number} berhasil ditolak.");
    }
}
