<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('quotations.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Buat Penawaran Harga Baru') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Rincikan produk, harga satuan, dan batas waktu penawaran untuk dikirim ke pelanggan.') }}</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        items: [
            { item_name: '', description: '', quantity: 1, unit_price: 0 }
        ],
        discount: 0,
        tax: 0,
        addItem() {
            this.items.push({ item_name: '', description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        subtotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
        },
        totalAmount() {
            return Math.max(0, (this.subtotal() - Number(this.discount || 0)) + Number(this.tax || 0));
        },
        formatRupiah(val) {
            return 'Rp' + Math.round(val).toLocaleString('id-ID');
        }
    }">
        <form action="{{ route('quotations.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informasi Utama Penawaran -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs space-y-4">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">{{ __('1. Informasi Pelanggan & Penawaran') }}</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Pelanggan') }} <span class="text-rose-500">*</span></label>
                        <select name="customer_id" required class="w-full text-xs rounded-xl border-gray-300 px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- {{ __('Pilih Pelanggan') }} --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $selectedCustomerId) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">
                            {{ __('Belum ada di daftar?') }} <a href="{{ route('customers.create') }}" target="_blank" class="text-indigo-600 underline font-semibold">{{ __('Tambah Pelanggan Baru') }}</a>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Masa Berlaku Penawaran') }}</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(14)->format('Y-m-d')) }}"
                               class="w-full text-xs rounded-xl border-gray-300 px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-[11px] text-gray-400 mt-1">{{ __('Batas waktu pelanggan dapat menyetujui penawaran ini secara online.') }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Judul / Ringkasan Penawaran') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="{{ __('Contoh: Penawaran 100 Pcs Kaos Polo Bordir Komunitas Mobil') }}"
                           class="w-full text-xs rounded-xl border-gray-300 px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Rincian Item Penawaran -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">{{ __('2. Rincian Item Penawaran') }}</h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Masukkan produk, bahan, jumlah, dan harga satuan.') }}</p>
                    </div>
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Tambah Baris Item') }}</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 bg-gray-50/60 rounded-xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700" x-text="'Item #' + (index + 1)"></span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="text-rose-600 hover:text-rose-800 text-xs font-semibold">
                                    {{ __('Hapus Baris') }}
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Nama Item / Produk') }} <span class="text-rose-500">*</span></label>
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required
                                           placeholder="{{ __('Contoh: Kaos Cotton Combed 30s') }}"
                                           class="w-full text-xs rounded-lg border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Spesifikasi / Rincian') }}</label>
                                    <input type="text" :name="'items[' + index + '][description]'" x-model="item.description"
                                           placeholder="{{ __('Contoh: Sablon DTF Dada A4 + Punggung A3') }}"
                                           class="w-full text-xs rounded-lg border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Kuantitas') }} <span class="text-rose-500">*</span></label>
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required
                                           class="w-full text-xs rounded-lg border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Harga Satuan (Rp)') }} <span class="text-rose-500">*</span></label>
                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" required
                                           class="w-full text-xs rounded-lg border-gray-300 px-3 py-2 bg-white">
                                </div>
                            </div>

                            <div class="text-right text-xs text-gray-500 font-medium">
                                {{ __('Subtotal Item:') }} <span class="font-bold text-gray-900" x-text="formatRupiah((item.quantity || 0) * (item.unit_price || 0))"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Kalkulasi Akhir -->
                <div class="p-4 bg-indigo-50/40 rounded-xl border border-indigo-100 flex flex-col sm:flex-row justify-between items-end gap-4 mt-4">
                    <div class="w-full sm:w-1/2 space-y-2">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Potongan / Diskon (Rp)') }}</label>
                                <input type="number" name="discount" x-model.number="discount" min="0"
                                       class="w-full text-xs rounded-lg border-gray-300 px-3 py-1.5 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">{{ __('Pajak / PPN (Rp)') }}</label>
                                <input type="number" name="tax" x-model.number="tax" min="0"
                                       class="w-full text-xs rounded-lg border-gray-300 px-3 py-1.5 bg-white">
                            </div>
                        </div>
                    </div>

                    <div class="text-right space-y-1">
                        <div class="text-xs text-gray-500">{{ __('Subtotal:') }} <span class="font-bold text-gray-800" x-text="formatRupiah(subtotal())"></span></div>
                        <div class="text-xs text-gray-500" x-show="discount > 0">{{ __('Diskon:') }} <span class="font-bold text-rose-600" x-text="'-' + formatRupiah(discount)"></span></div>
                        <div class="text-xs text-gray-500" x-show="tax > 0">{{ __('Pajak:') }} <span class="font-bold text-gray-800" x-text="'+' + formatRupiah(tax)"></span></div>
                        <div class="text-sm font-bold text-gray-600 pt-1 border-t border-indigo-200">
                            {{ __('Total Penawaran:') }} <span class="text-lg font-black text-indigo-700" x-text="formatRupiah(totalAmount())"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan / Syarat Ketentuan -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs space-y-3">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">{{ __('3. Catatan & Syarat Ketentuan') }}</h2>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Catatan untuk Pelanggan') }}</label>
                    <textarea name="notes" rows="4"
                              placeholder="{{ __('Contoh: DP minimal 50% sebelum produksi dimulai. Estimasi pengerjaan 7 hari kerja setelah desain disetujui.') }}"
                              class="w-full text-xs rounded-xl border-gray-300 px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('quotations.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    {{ __('Simpan Penawaran & Siapkan WA') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
