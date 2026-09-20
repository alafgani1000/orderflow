<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class TrackingController extends Controller
{
    /**
     * Display public order tracking page for customer.
     */
    public function show(string $token): View
    {
        $order = Order::with(['customer', 'payments', 'files', 'user'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('tracking.show', compact('order'));
    }
}
