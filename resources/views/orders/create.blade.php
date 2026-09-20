<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buat Pesanan Baru</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catat detail pesanan dari WhatsApp secara lengkap.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data" class="space-y-5" id="order-form">
            @csrf

            <!-- Section 1: Pelanggan & Nama Pesanan -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">1. Pelanggan & Nama Pesanan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pilih pemesan dan beri judul item pesanan.</p>
                </div>
                <div class="p-6 space-y-5">
                    <!-- Customer Selection with Fast AJAX Modal -->
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
                                this.errorMessage = 'Nama pelanggan wajib diisi.';
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
                                    throw new Error(data.message || 'Gagal menambahkan pelanggan.');
                                }
                                
                                // Injeksi opsi ke select pelanggan
                                const select = document.getElementById('customer_select');
                                if (select) {
                                    const opt = new Option(data.customer.display_text, data.customer.id, true, true);
                                    select.add(opt);
                                    select.value = data.customer.id;
                                    select.dispatchEvent(new Event('change'));
                                }

                                // Reset & Tutup
                                this.name = '';
                                this.phone = '';
                                this.address = '';
                                this.notes = '';
                                this.openModal = false;

                                // Feedback toast
                                if (window.showCustomerToast) {
                                    window.showCustomerToast('Pelanggan ' + data.customer.name + ' berhasil ditambahkan!');
                                }
                            } catch (err) {
                                this.errorMessage = err.message || 'Terjadi kesalahan sistem.';
                            } finally {
                                this.isSubmitting = false;
                            }
                        }
                    }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-semibold text-gray-700">Pilih Pelanggan <span class="text-red-500">*</span></label>
                            <button type="button" @click="openModal = true; errorMessage = '';" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>+ Pelanggan Baru</span>
                            </button>
                        </div>
                        
                        <select name="customer_id" id="customer_select" required
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('customer_id') border-red-400 @enderror">
                            <option value="">— Pilih pelanggan —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}{{ $customer->phone ? ' ('.$customer->phone.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

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
                                        <h3 class="text-sm font-bold text-gray-900">Tambah Pelanggan Cepat</h3>
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
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="name" required placeholder="Contoh: Bpk. Ahmad / PT. Berkah Jaya"
                                               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp</label>
                                        <input type="text" x-model="phone" placeholder="Contoh: 08123456789"
                                               class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat (Opsional)</label>
                                        <textarea x-model="address" rows="2" placeholder="Alamat pengiriman..."
                                                  class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                                        <button type="button" @click="openModal = false" :disabled="isSubmitting"
                                                class="px-3.5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                                            Batal
                                        </button>
                                        <button type="submit" :disabled="isSubmitting"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-xl shadow-xs transition disabled:opacity-50">
                                            <template x-if="isSubmitting">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Pilih'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Nama Pesanan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Nama Pesanan / Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="Contoh: 50 Pcs Kaos Komunitas / 100 Buku Yasin" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Spesifikasi Teknis</label>
                        <textarea name="description" rows="3"
                                  placeholder="Contoh: Cotton Combed 30s Hitam, Sablon DTF dada & punggung. Size M(20), L(20), XL(10)..."
                                  class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Biaya & DP -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">2. Biaya, Jumlah & Uang Muka (DP)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Total akan dihitung otomatis dari jumlah × harga satuan.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Jumlah (Pcs/Unit) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', 1) }}" required
                                   oninput="calculateTotal()"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Harga Satuan (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="price_per_unit" id="price_per_unit" min="0" step="500" value="{{ old('price_per_unit', 0) }}" required
                                   oninput="calculateTotal()"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Total Biaya (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_amount" id="total_amount" min="0" value="{{ old('total_amount', 0) }}" required
                                   oninput="calculateRemaining()"
                                   class="w-full rounded-xl border border-gray-300 bg-gray-50 font-bold px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-[11px] text-gray-400 mt-1">Dihitung otomatis atau edit manual.</p>
                        </div>
                    </div>

                    <!-- Size Breakdown Section -->
                    <div x-data="{ 
                        openSizes: {{ old('size_breakdown') ? 'true' : 'false' }},
                        customSizes: [] 
                    }" class="p-4 bg-gray-50 rounded-xl border border-gray-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                    <span>👕</span> Rincian Ukuran / Varian Kaos (Opsional)
                                </h3>
                                <p class="text-[11px] text-gray-400">Isi jika pesanan memiliki pecahan ukuran (S, M, L, dll). Total pcs otomatis dihitung.</p>
                            </div>
                            <button type="button" @click="openSizes = !openSizes" class="text-xs font-semibold text-indigo-600 hover:underline">
                                <span x-show="!openSizes">+ Buka Rincian Ukuran</span>
                                <span x-show="openSizes">− Tutup Rincian</span>
                            </button>
                        </div>

                        <div x-show="openSizes" x-cloak class="pt-2 border-t border-gray-200/60 space-y-3">
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2.5">
                                @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'] as $stdSize)
                                    <div>
                                        <label class="block text-center text-[10px] font-bold text-gray-600 mb-1">{{ $stdSize }}</label>
                                        <input type="number" 
                                               name="size_breakdown[{{ $stdSize }}]" 
                                               min="0" 
                                               value="{{ old('size_breakdown.' . $stdSize, 0) }}"
                                               class="size-input w-full text-center rounded-xl border border-gray-300 py-1.5 px-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               oninput="calculateSizes()">
                                    </div>
                                @endforeach
                            </div>

                            <!-- Custom sizes container -->
                            <template x-for="(cSize, index) in customSizes" :key="index">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'custom_size_name[' + index + ']'" x-model="cSize.name" placeholder="Ukuran (4XL/Anak-S)" class="w-40 text-xs rounded-xl border-gray-300 py-1.5 px-3">
                                    <input type="number" :name="'size_breakdown[' + (cSize.name || 'custom_' + index) + ']'" min="0" x-model="cSize.qty" class="size-input w-24 text-center text-xs rounded-xl border-gray-300 py-1.5" oninput="calculateSizes()">
                                    <button type="button" @click="customSizes.splice(index, 1); calculateSizes()" class="text-xs text-red-500 hover:underline">Hapus</button>
                                </div>
                            </template>

                            <div class="flex items-center justify-between pt-1">
                                <button type="button" @click="customSizes.push({ name: '', qty: 0 })" class="text-xs text-indigo-600 hover:underline font-semibold">
                                    + Tambah Ukuran Kustom Lainnya
                                </button>
                                <span class="text-xs text-gray-500 font-medium">Total Terhitung: <strong id="size-sum-display" class="text-indigo-600 font-bold">0</strong> pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- DP -->
                    <div class="pt-3 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Uang Muka / DP (Rp)</label>
                                <input type="number" name="dp_amount" id="dp_amount" min="0" value="{{ old('dp_amount', 0) }}"
                                       placeholder="0 jika belum ada DP" oninput="calculateRemaining()"
                                       class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[11px] text-gray-400 mt-1">Kosongkan jika belum ada DP.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Metode Bayar DP</label>
                                <select name="dp_method" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="cash">Cash / Tunai</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <!-- Live Calculation -->
                        <div class="mt-4 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-semibold text-indigo-800">Estimasi Sisa Tagihan:</span>
                                <span id="remaining-display" class="font-black text-lg text-indigo-700">Rp0</span>
                            </div>
                            <p class="text-[11px] text-indigo-500">Sisa pelunasan dapat dicatat setelah pesanan dibuat.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Deadline & File -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">3. Jadwal, Status & File Desain</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Tentukan deadline pengerjaan dan lampirkan file layout/desain.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Target Selesai / Deadline</label>
                            <input type="date" name="deadline" value="{{ old('deadline') }}"
                                   class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Status Awal <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', 'new') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Upload File Desain / Mockup</label>
                        <input type="file" name="design_files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.rar,.ai,.psd"
                               class="w-full text-sm text-gray-500 file:me-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-xl cursor-pointer">
                        <p class="text-[11px] text-gray-400 mt-1">JPG, PNG, PDF, ZIP, AI, PSD. Bisa upload lebih dari satu file.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan Khusus</label>
                        <textarea name="notes" rows="2"
                                  placeholder="Misal: Deadline ketat untuk acara tgl 28, kirim via kurir instan..."
                                  class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pb-4">
                <a href="{{ route('orders.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" id="submit-order-btn" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-bold shadow-md shadow-indigo-200/60 transition inline-flex items-center gap-2">
                    <svg id="submit-spinner" class="hidden w-4 h-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submit-btn-text">Simpan Pesanan</span>
                </button>
            </div>
        </form>
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
            calculateRemaining();
        }
        function calculateRemaining() {
            const total = parseFloat(document.getElementById('total_amount').value) || 0;
            const dp = parseFloat(document.getElementById('dp_amount').value) || 0;
            const remaining = Math.max(0, total - dp);
            document.getElementById('remaining-display').innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(remaining);
        }
        document.addEventListener('DOMContentLoaded', () => {
            calculateSizes();
            calculateRemaining();

            const form = document.getElementById('order-form');
            if (form) {
                form.addEventListener('submit', function() {
                    const btn = document.getElementById('submit-order-btn');
                    const spinner = document.getElementById('submit-spinner');
                    const btnText = document.getElementById('submit-btn-text');
                    if (btn && spinner && btnText) {
                        btn.classList.add('opacity-80', 'pointer-events-none');
                        spinner.classList.remove('hidden');
                        btnText.innerText = 'Menyimpan...';
                    }
                });
            }
        });
    </script>
</x-app-layout>
