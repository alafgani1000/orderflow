<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employees = $user->isOwner() ? $user->employees()->orderBy('name')->get() : collect();

        return view('settings.index', compact('user', 'employees'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:1000',
            'business_bank_name' => 'nullable|required_with:business_bank_account,business_bank_holder|string|max:100',
            'business_bank_account' => ['nullable', 'required_with:business_bank_name,business_bank_holder', 'string', 'max:100', 'regex:/^[0-9 .-]+$/'],
            'business_bank_holder' => 'nullable|required_with:business_bank_name,business_bank_account|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $user->update($validated);

        if ($user->isOwner() && filled($user->business_name) && filled($user->phone) && ! $user->onboarding_completed_at) {
            $user->update(['onboarding_completed_at' => now()]);
        }

        return back()->with('success', __('Profil berhasil diperbarui.'));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => __('Password saat ini tidak sesuai.')]);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', __('Password berhasil diubah.'));
    }
}
