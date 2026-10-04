<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasActiveSubscription()) {
            if ($request->routeIs('billing.*') || $request->routeIs('logout') || $request->routeIs('settings.*')) {
                return $next($request);
            }

            return redirect()->route('billing.index')
                ->with('error', __('Masa aktif paket langganan Anda telah berakhir. Silakan pilih atau perpanjang paket untuk melanjutkan akses.'));
        }

        return $next($request);
    }
}
