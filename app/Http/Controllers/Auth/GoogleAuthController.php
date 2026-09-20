<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman OAuth consent Google.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Konfigurasi Google OAuth belum lengkap: ' . $e->getMessage());
        }
    }

    /**
     * Menangani callback dari Google OAuth.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        // Pengguna membatalkan izin atau terjadi error pada Google OAuth
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Login dengan Google dibatalkan.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Gagal memproses autentikasi Google: ' . $e->getMessage());
        }

        if (!$googleUser || !$googleUser->getEmail()) {
            return redirect()->route('login')->with('error', 'Email tidak ditemukan dari akun Google.');
        }

        // 1. Cek apakah user dengan google_id ini sudah terdaftar
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Perbarui avatar jika ada perubahan
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }

            Auth::login($user, remember: true);
            return redirect()->intended(route('dashboard'));
        }

        // 2. Cek apakah user dengan email ini sudah ada (daftar manual sebelumnya)
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            // Hubungkan akun Google ke user yang sudah ada
            $existingUser->update([
                'google_id'         => $googleUser->getId(),
                'avatar'            => $existingUser->avatar ?: $googleUser->getAvatar(),
                'email_verified_at' => $existingUser->email_verified_at ?? now(),
            ]);

            Auth::login($existingUser, remember: true);
            return redirect()->intended(route('dashboard'))->with('success', 'Akun Google berhasil dihubungkan ke akun OrderFlow Anda!');
        }

        // 3. User baru -> buat akun Owner baru dan berikan Free Trial 14 hari
        $newUser = User::create([
            'name'              => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Pengguna Google'),
            'email'             => $googleUser->getEmail(),
            'google_id'         => $googleUser->getId(),
            'avatar'            => $googleUser->getAvatar(),
            'password'          => Hash::make(Str::random(32)),
            'role'              => User::ROLE_OWNER,
            'business_name'     => null,
            'email_verified_at' => now(),
        ]);

        // Berikan paket uji coba gratis (Free Trial 14 Hari Pro)
        $defaultPlan = Plan::where('slug', 'pro')->first() ?? Plan::first();
        if ($defaultPlan) {
            Subscription::create([
                'user_id'       => $newUser->id,
                'plan_id'       => $defaultPlan->id,
                'status'        => Subscription::STATUS_TRIALING,
                'starts_at'     => now(),
                'trial_ends_at' => now()->addDays(14),
                'notes'         => 'Free trial 14 hari saat registrasi via Google',
            ]);
        }

        event(new Registered($newUser));

        Auth::login($newUser, remember: true);

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang di OrderFlow! Akun Anda telah aktif dengan uji coba Pro 14 hari.');
    }
}
