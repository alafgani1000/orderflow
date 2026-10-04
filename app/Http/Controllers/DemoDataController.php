<?php

namespace App\Http\Controllers;

use App\Services\DemoDataService;
use Illuminate\Http\RedirectResponse;

class DemoDataController extends Controller
{
    public function store(DemoDataService $demoData): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isOwner(), 403, __('Hanya pemilik toko yang dapat mengelola data contoh.'));

        $result = $demoData->create($user);

        if (! $result['created']) {
            return back()->with('error', __('Data contoh sudah tersedia di toko Anda.'));
        }

        return redirect()->route('dashboard')->with('success', __('Data contoh berhasil dibuat: :customers pelanggan, :orders pesanan, dan :payments pembayaran.', [
            'customers' => $result['customers'],
            'orders' => $result['orders'],
            'payments' => $result['payments'],
        ]));
    }

    public function destroy(DemoDataService $demoData): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isOwner(), 403, __('Hanya pemilik toko yang dapat mengelola data contoh.'));

        $result = $demoData->delete($user);

        return redirect()->route('dashboard')->with('success', __('Data contoh berhasil dihapus. Data asli toko tetap aman.'));
    }
}
