<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_OWNER)
            ->with(['subscription.plan'])
            ->withCount(['orders', 'employees']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->whereHas('subscription', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $tenants = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $plans   = Plan::where('is_active', true)->get();

        return view('admin.tenants.index', compact('tenants', 'plans'));
    }

    public function show(User $tenant)
    {
        if (!$tenant->isOwner()) {
            abort(404);
        }

        $tenant->load(['subscription.plan', 'employees', 'orders' => fn($q) => $q->latest()->take(10)]);
        $plans = Plan::where('is_active', true)->get();

        return view('admin.tenants.show', compact('tenant', 'plans'));
    }

    public function updateSubscription(Request $request, User $tenant)
    {
        $validated = $request->validate([
            'plan_id'     => 'required|exists:plans,id',
            'status'      => 'required|in:trialing,active,past_due,expired,cancelled',
            'extend_days' => 'nullable|integer|min:0|max:365',
        ]);

        $subscription = $tenant->subscription;
        $plan = Plan::findOrFail($validated['plan_id']);

        $endsAt = now();
        if ($validated['status'] === Subscription::STATUS_ACTIVE) {
            $days = $validated['extend_days'] ?: 30;
            $endsAt = now()->addDays($days);
        }

        if (!$subscription) {
            Subscription::create([
                'user_id'       => $tenant->id,
                'plan_id'       => $plan->id,
                'status'        => $validated['status'],
                'starts_at'     => now(),
                'ends_at'       => $endsAt,
                'trial_ends_at' => $validated['status'] === Subscription::STATUS_TRIALING ? now()->addDays(14) : null,
                'notes'         => 'Diperbarui secara manual oleh Super Admin',
            ]);
        } else {
            $subscription->update([
                'plan_id'       => $plan->id,
                'status'        => $validated['status'],
                'ends_at'       => $validated['status'] === Subscription::STATUS_ACTIVE ? ($validated['extend_days'] ? now()->addDays($validated['extend_days']) : $subscription->ends_at ?? now()->addMonth()) : $subscription->ends_at,
                'trial_ends_at' => $validated['status'] === Subscription::STATUS_TRIALING ? now()->addDays(14) : null,
                'notes'         => 'Diperbarui secara manual oleh Super Admin',
            ]);
        }

        return back()->with('success', "Status langganan {$tenant->business_name} berhasil diperbarui.");
    }
}
