<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-gray-900">Lupa Kata Sandi?</h2>
        <p class="text-xs text-gray-500 mt-1">Masukkan alamat email Anda untuk menerima tautan reset kata sandi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email Terdaftar" />
            <x-text-input id="email" class="block mt-1 w-full text-sm" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-200 transition">
                Kirim Tautan Reset Password
            </button>
        </div>

        <div class="text-center pt-2">
            <a class="text-xs font-semibold text-gray-500 hover:text-indigo-600 hover:underline" href="{{ route('login') }}">
                ← Kembali ke halaman masuk
            </a>
        </div>
    </form>
</x-guest-layout>
