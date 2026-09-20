<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();

        $activeOrders = Order::where('user_id', $storeOwnerId)->active()->count();
        $dueToday     = Order::where('user_id', $storeOwnerId)->dueToday()->count();
        $overdue      = Order::where('user_id', $storeOwnerId)->overdue()->count();
        $unpaid       = Order::where('user_id', $storeOwnerId)->unpaid()->count();

        $upcomingOrders = Order::where('user_id', $storeOwnerId)
            ->active()
            ->whereNotNull('deadline')
            ->with('customer')
            ->orderBy('deadline')
            ->take(10)
            ->get();

        $monthlyRevenue = 0;
        $monthlyCashIn = 0;
        $totalPendingReceivables = 0;

        if (auth()->user()->canViewFinances()) {
            $monthlyRevenue = Order::where('user_id', $storeOwnerId)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');

            $grossCash = \App\Models\Payment::whereHas('order', fn($q) => $q->where('user_id', $storeOwnerId))
                ->where(fn($q) => $q->where('type', 'payment')->orWhereNull('type'))
                ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount');

            $refundCash = \App\Models\Payment::whereHas('order', fn($q) => $q->where('user_id', $storeOwnerId))
                ->where('type', 'refund')
                ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount');

            $monthlyCashIn = max(0, $grossCash - $refundCash);

            $totalPendingReceivables = Order::where('user_id', $storeOwnerId)
                ->whereNotIn('status', ['cancelled'])
                ->get()
                ->sum(fn($o) => $o->remaining_amount);
        }

        return view('dashboard', compact(
            'activeOrders',
            'dueToday',
            'overdue',
            'unpaid',
            'upcomingOrders',
            'monthlyRevenue',
            'monthlyCashIn',
            'totalPendingReceivables'
        ));
    }
}
