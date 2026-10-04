<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-gray-900">{{ __('Atur Ulang Kata Sandi') }}</h2>
        <p class="text-xs text-gray-500 mt-1">{{ __('Buat kata sandi baru untuk akun OrderFlow Anda.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-text-input id="email" class="block mt-1 w-full text-sm" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi Baru')" />
            <x-text-input id="password" class="block mt-1 w-full text-sm" type="password" name="password" required autocomplete="new-password" :placeholder="__('Minimal 8 karakter')" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full text-sm"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" :placeholder="__('Ketik ulang kata sandi baru')" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-200 transition">
                {{ __('Simpan Kata Sandi Baru') }}
            </button>
        </div>
    </form>
</x-guest-layout>
