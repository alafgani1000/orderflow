<x-app-layout>
    <x-slot name="header">
        <div class="max-w-2xl mx-auto flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tambah Pelanggan Baru</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catat informasi kontak untuk komunikasi dan update pesanan via WhatsApp.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Informasi Kontak Pelanggan</h2>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="p-6 space-y-4">
                @csrf

                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nama Pelanggan / Usaha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        placeholder="Misal: Budi Santoso / CV Maju Lancar" 
                        required 
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror"
                    >
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor WhatsApp -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nomor WhatsApp
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-emerald-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            placeholder="081234567890" 
                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-400 @enderror"
                        >
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Dapat format 08xxx atau 628xxx, otomatis disesuaikan untuk link WhatsApp.</p>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Alamat Pengiriman / Domisili
                    </label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="2" 
                        placeholder="Alamat lengkap pengiriman atau workshop pemesan..." 
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >{{ old('address') }}</textarea>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Catatan Khusus
                    </label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="2" 
                        placeholder="Misal: Langganan tetap, preferensi bahan tertentu, dll..." 
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >{{ old('notes') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs transition">
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
