<x-guest-layout :full="true">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white antialiased">
        
        <!-- KOLOM KIRI (DESKTOP): PRODUCT STORY -->
        <div class="hidden lg:flex lg:col-span-6 xl:col-span-7 relative bg-[#0b1026] text-white flex-col justify-between px-10 py-9 xl:px-16 xl:py-11 overflow-hidden">
            <!-- Layered backdrop -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_8%,rgba(99,102,241,0.24),transparent_34%),radial-gradient(circle_at_86%_82%,rgba(16,185,129,0.13),transparent_32%)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.025)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.025)_1px,transparent_1px)] [background-size:40px_40px] [mask-image:linear-gradient(to_bottom,black,transparent_88%)] pointer-events-none"></div>
            <div class="absolute top-0 right-0 h-full w-px bg-gradient-to-b from-transparent via-indigo-400/30 to-transparent"></div>

            <!-- Brand -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-white/[0.07] border border-white/10 flex items-center justify-center shadow-2xl shadow-indigo-950/50 backdrop-blur-sm">
                        <x-brand-mark class="w-9 h-9 shrink-0 transition-transform group-hover:scale-105" />
                    </div>
                    <div>
                        <span class="font-black text-xl tracking-tight text-white">Order<span class="text-indigo-400">Flow</span></span>
                        <span class="block text-[9px] uppercase font-bold tracking-[0.24em] text-slate-400 mt-0.5">Workshop OS</span>
                    </div>
                </a>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/[0.06] border border-white/10 text-[11px] font-semibold text-slate-300 backdrop-blur-sm">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-400"></span>
                    </span>
                    {{ __('Sistem siap digunakan') }}
                </div>
            </div>

            <!-- Product narrative -->
            <div class="relative z-10 my-auto py-8 xl:py-10 max-w-2xl">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 mb-4 text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-300">
                        <span class="w-7 h-px bg-indigo-400"></span>
                        {{ __('Dibuat untuk bisnis custom') }}
                    </div>
                    <h1 class="text-[2.5rem] xl:text-5xl font-black leading-[1.08] tracking-[-0.035em] text-white">
                        {{ __('Dari order masuk') }}<br>
                        {{ __('sampai lunas,') }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-violet-300 to-emerald-300">{{ __('semuanya mengalir.') }}</span>
                    </h1>
                    <p class="mt-4 max-w-lg text-sm xl:text-[15px] leading-7 text-slate-300">
                        {{ __('Satu ruang kerja untuk menjaga pesanan, produksi, deadline, dan pembayaran tetap tersusun—tanpa kehilangan konteks dari WhatsApp.') }}
                    </p>
                </div>

                <!-- Live workflow preview -->
                <div class="relative mt-7 max-w-xl">
                    <div class="absolute -inset-1 rounded-[1.65rem] bg-gradient-to-r from-indigo-500/25 via-violet-500/10 to-emerald-500/20 blur-xl"></div>
                    <div class="relative rounded-[1.5rem] border border-white/[0.12] bg-white/[0.075] p-5 xl:p-6 shadow-2xl shadow-black/25 backdrop-blur-xl">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/20 flex items-center justify-center text-indigo-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5L12 3l9 4.5M5 9v8.5L12 21l7-3.5V9M12 12l9-4.5M12 12L3 7.5M12 12v9"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-white">{{ __('Kaos Komunitas — 120 pcs') }}</span>
                                        <span class="rounded-md bg-white/[0.07] px-2 py-0.5 text-[9px] font-bold tracking-wide text-slate-300">ORD-0248</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-400">Nusantara Creative · {{ __('Deadline 18 Sep') }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full border border-amber-300/20 bg-amber-400/10 px-2.5 py-1 text-[10px] font-bold text-amber-200">{{ __('Produksi') }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-4 gap-2">
                            @foreach([
                                ['label' => 'Order', 'done' => true],
                                ['label' => __('Desain'), 'done' => true],
                                ['label' => __('Produksi'), 'done' => false],
                                ['label' => __('Selesai'), 'done' => false],
                            ] as $step)
                                <div>
                                    <div class="h-1.5 rounded-full {{ $step['done'] ? 'bg-emerald-400' : ($step['label'] === __('Produksi') ? 'bg-indigo-400' : 'bg-white/10') }}"></div>
                                    <p class="mt-2 text-[10px] font-semibold {{ $step['done'] ? 'text-emerald-300' : ($step['label'] === __('Produksi') ? 'text-indigo-200' : 'text-slate-500') }}">{{ $step['label'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-white/[0.08] pt-4">
                            <div class="flex items-center gap-6">
                                <div>
                                    <p class="text-[9px] uppercase tracking-wider text-slate-500">{{ __('Progress') }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-white">{{ __('65% selesai') }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] uppercase tracking-wider text-slate-500">{{ __('Pembayaran') }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-emerald-300">{{ __('DP diterima') }}</p>
                                </div>
                            </div>
                            <div class="flex -space-x-2">
                                <span class="w-7 h-7 rounded-full border-2 border-[#1b2140] bg-indigo-400 flex items-center justify-center text-[9px] font-black text-white">CS</span>
                                <span class="w-7 h-7 rounded-full border-2 border-[#1b2140] bg-violet-400 flex items-center justify-center text-[9px] font-black text-white">DS</span>
                                <span class="w-7 h-7 rounded-full border-2 border-[#1b2140] bg-emerald-400 flex items-center justify-center text-[9px] font-black text-[#0b1026]">PR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 text-[11px] font-medium text-slate-400">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Progres real-time') }}
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Laporan otomatis') }}
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Update WA 1-klik') }}
                    </span>
                </div>
            </div>

            <!-- Trust footer -->
            <div class="relative z-10 pt-5 border-t border-white/[0.08] flex items-center justify-between gap-4 text-[11px] text-slate-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ __('Data setiap toko terisolasi dan terlindungi') }}
                </div>
                <span>&copy; {{ date('Y') }} OrderFlow</span>
            </div>
        </div>

        <!-- KOLOM KANAN: FORM LOGIN BERSIH & MODERN -->
        <div class="lg:col-span-6 xl:col-span-5 relative flex flex-col justify-between p-6 sm:p-10 lg:p-12 xl:p-16 min-h-screen bg-gray-50/50">
            <div class="absolute top-6 right-6 sm:top-10 sm:right-10 lg:top-12 lg:right-12 xl:top-16 xl:right-16 hidden lg:block">
                <x-locale-switcher />
            </div>
            <!-- Mobile Brand Header (Tampil hanya di layar kecil / HP) -->
            <div class="lg:hidden flex items-center justify-between pb-6 border-b border-gray-200/60 mb-6">
                <a href="/" class="flex items-center gap-2.5">
                    <x-brand-mark class="w-9 h-9 shrink-0" />
                    <span class="font-extrabold text-xl tracking-tight text-gray-900">Order<span class="text-indigo-600">Flow</span></span>
                </a>
                <div class="flex items-center gap-2">
                    <x-locale-switcher />
                    <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold">Workshop OS</span>
                </div>
            </div>

            <!-- Login Form Container (Vertically Centered) -->
            <div class="my-auto max-w-md w-full mx-auto bg-white p-6 sm:p-8 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100">
                <!-- Header Form -->
                <div class="mb-6">
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Selamat Datang 👋') }}</h2>
                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                        {{ __('Masuk ke akun OrderFlow Anda untuk memantau produksi, pesanan baru, dan pembayaran hari ini.') }}
                    </p>
                </div>

                <!-- Session Status / Alerts -->
                <x-auth-session-status class="mb-5" :status="session('status')" />

                @if (session('success'))
                    <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Google Sign In Button -->
                <div class="mb-5">
                    <a href="{{ route('auth.google') }}" 
                       class="w-full flex items-center justify-center gap-3 px-4 py-2.5 bg-white hover:bg-gray-50 active:bg-gray-100 text-gray-700 text-xs sm:text-sm font-bold rounded-xl border border-gray-300 shadow-2xs hover:shadow-xs transition duration-150">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                            <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                            <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.1c0 2.8.7 5.4 1.9 7.8l3.7-2.9z"/>
                            <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/>
                        </svg>
                        <span>{{ __('Masuk dengan Google') }}</span>
                    </a>
                    <p class="mt-2 text-[11px] leading-relaxed text-center text-gray-400">
                        {!! __('Pengguna baru yang melanjutkan melalui Google menyetujui :terms dan :privacy.', [
                            'terms' => '<a href="'.route('terms').'" class="font-semibold text-indigo-600 hover:underline">'.__('Syarat & Ketentuan').'</a>',
                            'privacy' => '<a href="'.route('privacy').'" class="font-semibold text-indigo-600 hover:underline">'.__('Kebijakan Privasi').'</a>',
                        ]) !!}
                    </p>
                </div>

                <!-- Divider -->
                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-gray-400 font-medium">{{ __('atau masuk dengan email') }}</span>
                    </div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            {{ __('Alamat Email') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input id="email" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username" 
                                   placeholder="nama@email-usaha.com" 
                                   class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-400">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                {{ __('Kata Sandi') }}
                            </label>
                            @if (Route::has('password.request'))
                                <a class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline transition" href="{{ route('password.request') }}">
                                    {{ __('Lupa kata sandi?') }}
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" 
                                   :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password" 
                                   placeholder="••••••••" 
                                   class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-gray-400">
                            <!-- Toggle View Password Button -->
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition"
                                    title="{{ __('Tampilkan / Sembunyikan Password') }}">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me" 
                                   type="checkbox" 
                                   name="remember" 
                                   class="w-4 h-4 rounded-md border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500 cursor-pointer">
                            <span class="ms-2 text-xs font-medium text-gray-600">{{ __('Ingat saya di perangkat ini') }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-200/60 transition-all flex items-center justify-center gap-2 group">
                            <span>{{ __('Masuk ke Dashboard') }}</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Register CTA -->
                    <div class="pt-4 mt-4 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500">
                            {{ __('Belum memiliki akun usaha?') }}
                            <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline ms-1">
                                {{ __('Daftar Sekarang Gratis →') }}
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Helpful Customer Tracking Info Box -->
                <div class="mt-5 p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100 text-[11px] text-indigo-900 flex items-start gap-2.5">
                    <span class="text-base shrink-0">💡</span>
                    <div>
                        <span class="font-bold">{{ __('Pelanggan ingin melacak pesanan?') }}</span>
                        <p class="text-indigo-700 mt-0.5">
                            {{ __('Gunakan tautan pelacakan langsung yang dikirimkan via WhatsApp toko tanpa perlu login ke sistem ini.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-6 text-center text-xs text-gray-400">
                <span>OrderFlow — {{ __('Solusi Manajemen Produksi & Pesanan WhatsApp') }}</span>
            </div>
        </div>

    </div>
</x-guest-layout>
