<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-gray-900">Daftar Akun OrderFlow</h2>
        <p class="text-xs text-gray-500 mt-1">Aplikasi sederhana kelola pesanan, deadline, & pembayaran WhatsApp.</p>
    </div>

    <!-- Google Sign Up Button -->
    <div class="mb-5">
        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 px-4 py-2.5 bg-white hover:bg-gray-50 active:bg-gray-100 text-gray-700 text-xs sm:text-sm font-bold rounded-xl border border-gray-300 shadow-2xs hover:shadow-xs transition duration-150">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.1c0 2.8.7 5.4 1.9 7.8l3.7-2.9z"/>
                <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/>
            </svg>
            <span>Daftar Cepat dengan Google</span>
        </a>
    </div>

    <!-- Divider -->
    <div class="relative my-5">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-white px-3 text-gray-400 font-medium">atau daftar dengan formulir</span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Nama Pemilik -->
        <div>
            <x-input-label for="name" value="Nama Lengkap *" />
            <x-text-input id="name" class="block mt-1 w-full text-sm" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Budi Prasetyo" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Nama Usaha -->
        <div>
            <x-input-label for="business_name" value="Nama Usaha / Toko / Konveksi" />
            <x-text-input id="business_name" class="block mt-1 w-full text-sm" type="text" name="business_name" :value="old('business_name')" placeholder="Contoh: Sablon Juara / Berkah Printing" />
            <x-input-error :messages="$errors->get('business_name')" class="mt-1" />
        </div>

        <!-- Nomor WhatsApp -->
        <div>
            <x-input-label for="phone" value="Nomor WhatsApp Usaha" />
            <x-text-input id="phone" class="block mt-1 w-full text-sm" type="text" name="phone" :value="old('phone')" placeholder="Contoh: 08123456789" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email (Login) *" />
            <x-text-input id="email" class="block mt-1 w-full text-sm" type="email" name="email" :value="old('email')" required placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi (Password) *" />
            <x-text-input id="password" class="block mt-1 w-full text-sm"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Ulangi Kata Sandi *" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full text-sm"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-200 transition">
                Daftar & Masuk Dashboard
            </button>
        </div>

        <div class="text-center pt-2">
            <span class="text-xs text-gray-500">Sudah punya akun?</span>
            <a class="text-xs font-bold text-indigo-600 hover:underline ms-1" href="{{ route('login') }}">
                Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>
