<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-gray-900">{{ __('Konfirmasi Kata Sandi') }}</h2>
        <p class="text-xs text-gray-500 mt-1">{{ __('Ini adalah area aman. Harap konfirmasikan kata sandi Anda sebelum melanjutkan.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi (Password)')" />
            <x-text-input id="password" class="block mt-1 w-full text-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md shadow-indigo-200 transition">
                {{ __('Konfirmasi') }}
            </button>
        </div>
    </form>
</x-guest-layout>
