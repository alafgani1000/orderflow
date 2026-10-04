<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Services\SecureFileStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isSuperAdmin()) {
            abort(403, __('Hanya pemilik toko yang dapat mengelola langganan.'));
        }

        $subscription = $user->currentSubscription();
        $currentPlan = $subscription?->plan;
        $plans = Plan::where('is_active', true)->orderBy('price')->get();

        $invoices = SubscriptionInvoice::where('user_id', $user->getStoreOwnerId())
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();

        $ordersCount = $user->currentMonthOrdersCount();
        $staffCount = $user->currentEmployeesCount();

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
        if (! $user->isOwner() && ! $user->isSuperAdmin()) {
            abort(403, __('Hanya pemilik toko yang dapat mengelola langganan.'));
        }

        // Jika memilih paket gratis (Starter)
        if ($plan->isFree()) {
            $subscription = $user->subscription;
            if (! $subscription) {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'ends_at' => null,
                ]);
            } else {
                $subscription->update([
                    'plan_id' => $plan->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'ends_at' => null,
                ]);
            }

            return redirect()->route('billing.index')
                ->with('success', __('Paket toko berhasil diubah ke :plan.', ['plan' => $plan->name]));
        }

        $billingAccounts = $this->configuredBillingAccounts();
        $paymentMethods = $this->availablePaymentMethods($billingAccounts);

        return view('billing.checkout', compact('plan', 'billingAccounts', 'paymentMethods'));
    }

    /**
     * Owner views the payment proof they submitted (authorized stream, never a public URL).
     */
    public function proof(SubscriptionInvoice $invoice, SecureFileStorage $secureFiles)
    {
        $user = auth()->user();
        abort_unless($user->isOwner() && (int) $invoice->user_id === (int) $user->id, 404);
        abort_if(blank($invoice->payment_proof), 404);

        return $secureFiles->response(
            $invoice->payment_proof,
            $invoice->invoice_number.'.'.pathinfo($invoice->payment_proof, PATHINFO_EXTENSION),
            preferInline: true,
        );
    }

    public function confirmPayment(Request $request, SecureFileStorage $secureFiles)
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isSuperAdmin()) {
            abort(403, __('Hanya pemilik toko yang dapat mengonfirmasi pembayaran.'));
        }

        $billingAccounts = $this->configuredBillingAccounts();
        $paymentMethods = $this->availablePaymentMethods($billingAccounts);

        if ($paymentMethods === []) {
            throw ValidationException::withMessages([
                'payment_method' => __('Metode pembayaran belum dikonfigurasi. Hubungi tim dukungan OrderFlow.'),
            ]);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => ['required', 'string', 'max:50', Rule::in(array_keys($paymentMethods))],
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $secureFiles->store($request->file('payment_proof'), "subscription-proofs/{$user->id}");
        }

        $subscription = $user->subscription;

        $invoice = SubscriptionInvoice::create([
            'subscription_id' => $subscription?->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
            'amount' => $plan->price,
            'payment_method' => $validated['payment_method'],
            'status' => SubscriptionInvoice::STATUS_PENDING,
            'payment_proof' => $proofPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('billing.index')
            ->with('success', __('Konfirmasi pembayaran langganan berhasil dikirim (Invoice: :invoice). Tim kami akan memverifikasi dalam waktu 1x24 jam.', ['invoice' => $invoice->invoice_number]));
    }

    private function configuredBillingAccounts(): array
    {
        return collect(config('orderflow.billing.accounts', []))
            ->filter(fn (array $account): bool => filled($account['number'] ?? null) && filled($account['holder'] ?? null))
            ->values()
            ->all();
    }

    private function availablePaymentMethods(array $billingAccounts): array
    {
        $methods = collect($billingAccounts)->mapWithKeys(function (array $account): array {
            $value = match ($account['code'] ?? null) {
                'bca' => 'Transfer BCA',
                'mandiri' => 'Transfer Mandiri',
                default => 'Transfer '.($account['bank'] ?? 'Bank'),
            };
            $label = match ($account['code'] ?? null) {
                'bca' => __('Transfer Bank BCA'),
                'mandiri' => __('Transfer Bank Mandiri'),
                default => __('Transfer :bank', ['bank' => $account['bank']]),
            };

            return [$value => $label];
        })->all();

        if (config('orderflow.billing.qris_enabled')) {
            $methods['QRIS'] = 'QRIS (GoPay, OVO, ShopeePay, Dana)';
        }

        return $methods;
    }
}
