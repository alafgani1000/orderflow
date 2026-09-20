<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTenants = User::where('role', User::ROLE_OWNER)->count();
        
        $activeSubscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->count();

        $trialSubscriptions = Subscription::where('status', Subscription::STATUS_TRIALING)
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')->orWhere('trial_ends_at', '>', now());
            })
            ->count();

        $expiredSubscriptions = Subscription::whereIn('status', [Subscription::STATUS_EXPIRED, Subscription::STATUS_PAST_DUE])
            ->orWhere(function ($q) {
                $q->where('status', Subscription::STATUS_TRIALING)->where('trial_ends_at', '<=', now());
            })
            ->orWhere(function ($q) {
                $q->where('status', Subscription::STATUS_ACTIVE)->whereNotNull('ends_at')->where('ends_at', '<=', now());
            })
            ->count();

        // Estimasi MRR (Monthly Recurring Revenue) dari subscription aktif
        $estimatedMRR = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->with('plan')
            ->get()
            ->sum(fn($sub) => $sub->plan?->price ?? 0);

        $totalPlatformOrders = Order::count();
        $pendingPaymentsCount = SubscriptionInvoice::where('status', SubscriptionInvoice::STATUS_PENDING)->count();

        $recentTenants = User::where('role', User::ROLE_OWNER)
            ->with(['subscription.plan'])
            ->withCount(['orders', 'employees'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $pendingInvoices = SubscriptionInvoice::where('status', SubscriptionInvoice::STATUS_PENDING)
            ->with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalTenants',
            'activeSubscriptions',
            'trialSubscriptions',
            'expiredSubscriptions',
            'estimatedMRR',
            'totalPlatformOrders',
            'pendingPaymentsCount',
            'recentTenants',
            'pendingInvoices'
        ));
    }
}
