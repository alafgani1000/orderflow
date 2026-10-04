<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        abort_unless($user->isOwner(), 403, __('Hanya pemilik toko yang dapat mengatur onboarding.'));

        return view('onboarding.show', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user->isOwner(), 403, __('Hanya pemilik toko yang dapat mengatur onboarding.'));

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'business_bank_name' => ['nullable', 'required_with:business_bank_account,business_bank_holder', 'string', 'max:100'],
            'business_bank_account' => ['nullable', 'required_with:business_bank_name,business_bank_holder', 'string', 'max:100', 'regex:/^[0-9 .-]+$/'],
            'business_bank_holder' => ['nullable', 'required_with:business_bank_name,business_bank_account', 'string', 'max:255'],
        ]);

        $user->update([
            ...$validated,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('Profil toko berhasil disiapkan. Berikutnya, tambahkan pelanggan dan pesanan pertama Anda.'));
    }
}
