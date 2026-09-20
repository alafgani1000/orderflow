<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya pemilik toko yang dapat mengelola langganan.');
        }

        $subscription = $user->currentSubscription();
        $currentPlan  = $subscription?->plan;
        $plans        = Plan::where('is_active', true)->orderBy('price')->get();

        $invoices = SubscriptionInvoice::where('user_id', $user->getStoreOwnerId())
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();

        $ordersCount = $user->currentMonthOrdersCount();
        $staffCount  = $user->currentEmployeesCount();

        return view('billing.index', compact(
            'subscription',
            'currentPlan',
            'plans',
            'invoices',
            'ordersCount',
            'staffCount'
        ));
    }

    public function checkout(Plan $plan)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya pemilik toko yang dapat mengelola langganan.');
        }

        // Jika memilih paket gratis (Starter)
        if ($plan->isFree()) {
            $subscription = $user->subscription;
            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status'  => Subscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'ends_at' => null,
                ]);
            } else {
                $subscription->update([
                    'plan_id' => $plan->id,
                    'status'  => Subscription::STATUS_ACTIVE,
                    'ends_at' => null,
                ]);
            }

            return redirect()->route('billing.index')
                ->with('success', "Paket toko berhasil diubah ke {$plan->name}.");
        }

        return view('billing.checkout', compact('plan'));
    }

    public function confirmPayment(Request $request)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya pemilik toko yang dapat mengonfirmasi pembayaran.');
        }

        $validated = $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'payment_method' => 'required|string|max:50',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'          => 'nullable|string|max:500',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('subscription-proofs', 'public');
        }

        $subscription = $user->subscription;

        $invoice = SubscriptionInvoice::create([
            'subscription_id' => $subscription?->id,
            'user_id'         => $user->id,
            'plan_id'         => $plan->id,
            'invoice_number'  => SubscriptionInvoice::generateInvoiceNumber(),
            'amount'          => $plan->price,
            'payment_method'  => $validated['payment_method'],
            'status'          => SubscriptionInvoice::STATUS_PENDING,
            'payment_proof'   => $proofPath,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()->route('billing.index')
            ->with('success', 'Konfirmasi pembayaran langganan berhasil dikirim (Invoice: ' . $invoice->invoice_number . '). Tim kami akan memverifikasi dalam waktu 1x24 jam.');
    }
}
