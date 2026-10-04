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
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'business_name' => $request->business_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_OWNER,
            'locale' => $request->session()->get('locale', config('app.locale')),
            'terms_accepted_at' => now(),
            'terms_version' => config('orderflow.legal.terms_version'),
            'privacy_version' => config('orderflow.legal.privacy_version'),
        ]);

        // Berikan paket uji coba gratis (Free Trial 14 Hari)
        $defaultPlan = Plan::where('slug', 'pro')->first() ?? Plan::first();
        if ($defaultPlan) {
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $defaultPlan->id,
                'status' => Subscription::STATUS_TRIALING,
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'notes' => 'Free trial 14 hari saat registrasi awal',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('onboarding.show', absolute: false));
    }
}
