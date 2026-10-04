<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-wider">{{ __('Langkah 1 dari 3') }}</span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Siapkan Toko Anda') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Lengkapi informasi dasar agar invoice dan komunikasi pelanggan tampil profesional.') }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-indigo-600">
                {{ __('Lewati untuk sekarang') }} &rarr;
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-5">
        <div class="grid grid-cols-3 gap-2" aria-label="{{ __('Progres onboarding') }}">
            <div class="h-2 rounded-full bg-indigo-600"></div>
            <div class="h-2 rounded-full bg-gray-200"></div>
            <div class="h-2 rounded-full bg-gray-200"></div>
        </div>

        <form method="POST" action="{{ route('onboarding.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <section class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-black shrink-0">1</span>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">{{ __('Identitas Toko') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Informasi ini akan muncul pada invoice, SPK, dan pesan pelanggan.') }}</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="business_name" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Nama Usaha / Toko') }} <span class="text-rose-500">*</span></label>
                        <input id="business_name" type="text" name="business_name" value="{{ old('business_name', $user->business_name) }}" required
                               placeholder="{{ __('Contoh: Sablon Juara') }}"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('business_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Nomor WhatsApp Usaha') }} <span class="text-rose-500">*</span></label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                               placeholder="{{ __('Contoh: 08123456789') }}"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="business_address" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Alamat Usaha') }}</label>
                        <textarea id="business_address" name="business_address" rows="3"
                                  placeholder="{{ __('Alamat yang akan dicantumkan pada dokumen toko.') }}"
                                  class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('business_address', $user->business_address) }}</textarea>
                        @error('business_address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-sm font-black shrink-0">2</span>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">{{ __('Rekening Pembayaran Toko') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Opsional. Simpan rekening tujuan pelunasan pelanggan untuk digunakan pada dokumen dan pesan toko.') }}</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="business_bank_name" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Nama Bank') }}</label>
                        <input id="business_bank_name" type="text" name="business_bank_name" value="{{ old('business_bank_name', $user->business_bank_name) }}"
                               placeholder="BCA / Mandiri / BRI"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('business_bank_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="business_bank_account" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Nomor Rekening') }}</label>
                        <input id="business_bank_account" type="text" name="business_bank_account" value="{{ old('business_bank_account', $user->business_bank_account) }}"
                               placeholder="1234567890"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('business_bank_account') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="business_bank_holder" class="block text-xs font-bold text-gray-700 mb-1.5">{{ __('Nama Pemilik Rekening') }}</label>
                        <input id="business_bank_holder" type="text" name="business_bank_holder" value="{{ old('business_bank_holder', $user->business_bank_holder) }}"
                               placeholder="{{ __('Nama sesuai rekening') }}"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('business_bank_holder') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-sm font-black shrink-0">3</span>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">{{ __('Kenali Alur Produksi') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Pesanan akan bergerak melalui tahapan berikut agar seluruh tim memahami progresnya.') }}</p>
                    </div>
                </div>
                <div class="p-6">
                    @php($workflowStatuses = collect(\App\Models\Order::STATUSES)->except('cancelled'))
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($workflowStatuses as $label)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                {{ __($label) }}
                            </span>
                            @if(!$loop->last)<span class="text-gray-300">→</span>@endif
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-gray-500">{{ __('Setelah profil tersimpan, checklist di dashboard akan memandu Anda membuat pelanggan dan pesanan pertama.') }}</p>
                </div>
            </section>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pb-6">
                <p class="text-[11px] text-gray-400">{{ __('Informasi ini dapat diperbarui kembali dari menu Pengaturan.') }}</p>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-md shadow-indigo-200 transition">
                    {{ __('Simpan & Lanjutkan') }}
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
