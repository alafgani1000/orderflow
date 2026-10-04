<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <a href="{{ route('orders.show', $order) }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Edit Pesanan #:number', ['number' => $order->order_number]) }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Perbarui rincian atau spesifikasi pesanan.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <script>
            window.orderFormMessages = @json([
                'customerRequired' => __('Nama pelanggan wajib diisi.'),
                'customerFailed' => __('Gagal menambahkan pelanggan.'),
                'systemError' => __('Terjadi kesalahan sistem.'),
                'saving' => __('Menyimpan...'),
                'saveAndSelect' => __('Simpan & Pilih'),
            ]);
        </script>
        <form method="POST" action="{{ route('orders.update', $order) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Section 1 -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">{{ __('1. Pelanggan & Nama Pesanan') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div x-data="{
                        openModal: false,
                        name: '',
                        phone: '',
                        address: '',
                        notes: '',
                        isSubmitting: false,
                        errorMessage: '',
                        async submitCustomer() {
                            if (!this.name.trim()) {
                                this.errorMessage = window.orderFormMessages.customerRequired;
                                return;
                            }
                            this.isSubmitting = true;
                            this.errorMessage = '';
                            try {
                                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                                const res = await fetch('{{ route('customers.store') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        name: this.name.trim(),
                                        phone: this.phone.trim(),
                                        address: this.address.trim(),
                                        notes: this.notes.trim()
                                    })
                                });
                                const data = await res.json();
                                if (!res.ok) {
                                    throw new Error(data.message || window.orderFormMessages.customerFailed);
                                }
                                
                                const select = document.getElementById('edit_customer_select');
                                if (select) {
                                    const opt = new Option(data.customer.display_text, data.customer.id, true, true);
                                    select.add(opt);
                                    select.value = data.customer.id;
                                    select.dispatchEvent(new Event('change'));
                                }

                                this.name = '';
                                this.phone = '';
                                this.address = '';
                                this.notes = '';
                                this.openModal = false;
                            } catch (err) {
                                this.errorMessage = err.message || window.orderFormMessages.systemError;
                            } finally {
                                this.isSubmitting = false;
                            }
                        }
                    }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-gray-700">{{ __('Pelanggan') }} <span class="text-red-500">*</span></label>
                            <button type="button" @click="openModal = true; errorMessage = '';" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>{{ __('+ Pelanggan Baru') }}</span>
                            </button>
                        </div>
                        <select name="customer_id" id="edit_customer_select" required class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}{{ $customer->phone ? ' ('.$customer->phone.')' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Modal Tambah Pelanggan Instan -->
                        <div x-show="openModal" x-cloak
                             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                            <div @click.outside="if(!isSubmitting) openModal = false"
                                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">{{ __('Tambah Pelanggan Cepat') }}</h3>
                                    </div>
                                    <button type="button" @click="if(!isSubmitting) openModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <template x-if="errorMessage">
                                    <div class="mb-3.5 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span x-text="errorMessage"></span>
                                    </div>
                                </template>

                                <form @submit.prevent="submitCustomer()" class="space-y-3.5">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Nama Pelanggan') }} <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="name" required placeholder="{{ __('Contoh: Bpk. Ahmad / PT. Berkah Jaya') }}"
                                               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Nomor WhatsApp') }}</label>
                                        <input type="text" x-model="phone" placeholder="{{ __('Contoh: 08123456789') }}"
                                               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Alamat (Opsional)') }}</label>
                                        <textarea x-model="address" rows="2" placeholder="{{ __('Alamat pengiriman...') }}"
                                                  class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                                        <button type="button" @click="openModal = false" :disabled="isSubmitting"
                                                class="px-3.5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                                            {{ __('Batal') }}
                                        </button>
                                        <button type="submit" :disabled="isSubmitting"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-xl shadow-xs transition disabled:opacity-50">
                                            <template x-if="isSubmitting">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <span x-text="isSubmitting ? window.orderFormMessages.saving : window.orderFormMessages.saveAndSelect"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Nama Pesanan') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $order->name) }}" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Spesifikasi Teknis') }}</label>
                        <textarea name="description" rows="3"
                                  class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $order->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2 -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">{{ __('2. Biaya & Jumlah') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Jumlah (Pcs)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', $order->quantity) }}" required
                                   oninput="calculateTotal()"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Harga Satuan (Rp)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="price_per_unit" id="price_per_unit" min="0" value="{{ old('price_per_unit', $order->price_per_unit) }}" required
                                   oninput="calculateTotal()"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Total Biaya (Rp)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="total_amount" id="total_amount" min="0" value="{{ old('total_amount', $order->total_amount) }}" required
                                   class="w-full rounded-xl border border-gray-300 bg-gray-50 font-bold px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-400 mt-1">{{ __('Dihitung otomatis atau edit manual.') }}</p>
                        </div>
                    </div>

                    <!-- Size Breakdown Section -->
                    @php
                        $existingSizes = old('size_breakdown', $order->size_breakdown ?? []);
                        $stdList = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
                        $customItems = [];
                        if (is_array($existingSizes)) {
                            foreach ($existingSizes as $k => $v) {
                                if (!in_array($k, $stdList) && $v > 0) {
                                    $customItems[] = ['name' => $k, 'qty' => (int)$v];
                                }
                            }
                        }
                    @endphp
                    <div x-data="{ 
                        openSizes: {{ !empty($existingSizes) ? 'true' : 'false' }},
                        customSizes: {{ json_encode($customItems) }} 
                    }" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                    <span>👕</span> {{ __('Rincian Ukuran / Varian Kaos (Opsional)') }}
                                </h3>
                                <p class="text-[11px] text-gray-400">{{ __('Total pcs akan otomatis terupdate saat ukuran diubah.') }}</p>
                            </div>
                            <button type="button" @click="openSizes = !openSizes" class="text-xs font-semibold text-indigo-600 hover:underline">
                                <span x-show="!openSizes">+ {{ __('Buka Rincian Ukuran') }}</span>
                                <span x-show="openSizes">− {{ __('Tutup Rincian') }}</span>
                            </button>
                        </div>

                        <div x-show="openSizes" x-cloak class="pt-2 border-t border-gray-200/60 space-y-3">
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                                @foreach($stdList as $stdSize)
                                    <div>
                                        <label class="block text-center text-[10px] font-bold text-gray-600 mb-1">{{ $stdSize }}</label>
                                        <input type="number" 
                                               name="size_breakdown[{{ $stdSize }}]" 
                                               min="0" 
                                               value="{{ $existingSizes[$stdSize] ?? 0 }}"
                                               class="size-input w-full text-center rounded-xl border border-gray-300 py-1.5 px-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               oninput="calculateSizes()">
                                    </div>
                                @endforeach
                            </div>

                            <!-- Custom sizes container -->
                            <template x-for="(cSize, index) in customSizes" :key="index">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'custom_size_name[' + index + ']'" x-model="cSize.name" placeholder="{{ __('Ukuran (4XL/Anak-S)') }}" class="w-40 text-xs rounded-xl border-gray-300 py-1.5 px-3">
                                    <input type="number" :name="'size_breakdown[' + (cSize.name || 'custom_' + index) + ']'" min="0" x-model="cSize.qty" class="size-input w-24 text-center text-xs rounded-xl border-gray-300 py-1.5" oninput="calculateSizes()">
                                    <button type="button" @click="customSizes.splice(index, 1); calculateSizes()" class="text-xs text-red-500 hover:underline">{{ __('Hapus') }}</button>
                                </div>
                            </template>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button" @click="customSizes.push({ name: '', qty: 0 })" class="text-xs text-indigo-600 hover:underline font-semibold">
                                    + {{ __('Tambah Ukuran Kustom Lainnya') }}
                                </button>
                                <span class="text-xs text-gray-500 font-medium">{{ __('Total Terhitung:') }} <strong id="size-sum-display" class="text-indigo-600 font-bold">0</strong> pcs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">{{ __('3. Jadwal & Status') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Target Selesai / Deadline') }}</label>
                            <input type="date" name="deadline" value="{{ old('deadline', $order->deadline?->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Status') }} <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', $order->status) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Catatan Khusus') }}</label>
                        <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pb-4">
                @if(auth()->user()->isOwner())
                    <button type="button"
                            onclick='if(confirm(@js(__("Yakin ingin menghapus pesanan ini?")))) document.getElementById("delete-order-form").submit();'
                            class="px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition">
                        {{ __('Hapus Pesanan') }}
                    </button>
                @else
                    <div></div>
                @endif
                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.show', $order) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-sm shadow-indigo-200/60 transition">
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </div>
        </form>

        @if(auth()->user()->isOwner())
            <form id="delete-order-form" action="{{ route('orders.destroy', $order) }}" method="POST" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endif
    </div>

    <script>
        function calculateSizes() {
            let sum = 0;
            document.querySelectorAll('.size-input').forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                sum += val;
            });
            const sumDisplay = document.getElementById('size-sum-display');
            if (sumDisplay) sumDisplay.innerText = sum;
            if (sum > 0) {
                document.getElementById('quantity').value = sum;
                calculateTotal();
            }
        }
        function calculateTotal() {
            const qty = parseFloat(document.getElementById('quantity').value) || 0;
            const price = parseFloat(document.getElementById('price_per_unit').value) || 0;
            document.getElementById('total_amount').value = qty * price;
        }
        document.addEventListener('DOMContentLoaded', () => {
            calculateSizes();
        });
    </script>
</x-app-layout>
