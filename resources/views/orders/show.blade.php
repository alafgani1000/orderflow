<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold text-gray-900">Pesanan #{{ $order->order_number }}</h1>
                        <x-status-badge :status="$order->status" />
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Dibuat pada {{ $order->created_at->translatedFormat('l, d F Y - H:i') }} WIB</p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2" x-data="{
                copied: false,
                copyText(text) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    });
                }
            }">
                <!-- WA Status Button -->
                <a href="{{ $waStatusUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>Kirim Status WA</span>
                </a>

                <!-- Salin Pesan WA (1-Click Clipboard) -->
                <button type="button" @click="copyText(@js($order->remaining_amount > 0 ? $waReminderText : $waStatusText))"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer"
                        title="Salin template teks WhatsApp ke clipboard">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span x-text="copied ? 'Tersalin! ✓' : 'Salin Pesan WA'"></span>
                </button>

                <!-- WA Reminder Button for Unpaid Orders -->
                @if($order->remaining_amount > 0)
                    <a href="{{ $waReminderUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-semibold text-xs rounded-xl shadow-xs transition">
                        <span>💬 Tagih Sisa via WA</span>
                    </a>
                @endif

                @if($order->status === 'completed')
                    <a href="{{ $waCompletedUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                        🎉 Notif Selesai WA
                    </a>
                @endif

                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Invoice / SPK</span>
                </a>

                <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Edit Pesanan</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- Status Pipeline & Quick Status Changer -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Alur Pengerjaan Workshop</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Ubah status pesanan secara bertahap atau langsung pilih status tujuan.</p>
                </div>

                <!-- Status Update Form -->
                <form action="{{ route('orders.update-status', $order) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    
                    @if($order->next_status)
                        <button type="submit" name="status" value="{{ $order->next_status }}" class="inline-flex items-center gap-1 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                            <span>Lanjut: {{ $order->next_status_label }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                            Pilih Status Lain ▾
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute end-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-20" style="display: none;">
                            @foreach(\App\Models\Order::STATUSES as $sKey => $sLabel)
                                <button type="submit" name="status" value="{{ $sKey }}" class="w-full text-start px-3.5 py-2 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition flex items-center justify-between {{ $order->status === $sKey ? 'font-bold bg-gray-50' : '' }}">
                                    <span>{{ $sLabel }}</span>
                                    @if($order->status === $sKey)
                                        <span class="text-indigo-600 font-bold">✓</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            <!-- Visual Pipeline Tracker -->
            @php
                $pipelineSteps = [
                    'new' => '1. Baru',
                    'waiting_design' => '2. Desain',
                    'design_approved' => '3. Disetujui',
                    'production' => '4. Produksi',
                    'completed' => '5. Selesai',
                    'delivered' => '6. Diambil',
                ];
                $stepKeys = array_keys($pipelineSteps);
                $currentIndex = array_search($order->status, $stepKeys);
                if ($order->status === 'cancelled') {
                    $currentIndex = -1;
                }
            @endphp

            @if($order->status === 'cancelled')
                <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    Pesanan ini berstatus DIBATALKAN.
                </div>
            @else
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                    @foreach($pipelineSteps as $pKey => $pLabel)
                        @php
                            $stepIndex = array_search($pKey, $stepKeys);
                            $isDone = $currentIndex !== false && $stepIndex <= $currentIndex;
                            $isCurrent = $pKey === $order->status;
                        @endphp
                        <div class="p-2.5 rounded-xl border text-center transition {{ $isCurrent ? 'bg-indigo-50 border-indigo-300 ring-2 ring-indigo-500/20' : ($isDone ? 'bg-emerald-50/50 border-emerald-200' : 'bg-gray-50 border-gray-200 opacity-60') }}">
                            <div class="text-xs font-bold {{ $isCurrent ? 'text-indigo-700' : ($isDone ? 'text-emerald-700' : 'text-gray-500') }}">
                                {{ $pLabel }}
                            </div>
                            <div class="text-[10px] mt-0.5 {{ $isCurrent ? 'text-indigo-600 font-semibold' : ($isDone ? 'text-emerald-600' : 'text-gray-400') }}">
                                {{ $isCurrent ? 'Sedang Berjalan' : ($isDone ? '✓ Selesai' : 'Menunggu') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 2 Column Layout: Details & Sidebar Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Cols: Order & Financial Details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Order Specifications -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-900">Rincian Item Pesanan</h2>
                    </div>
                    
                    <div class="p-5 sm:p-6 space-y-4">
                        <div>
                            <span class="text-[11px] uppercase font-bold text-gray-400">Nama Pekerjaan</span>
                            <div class="text-base font-bold text-gray-900 mt-0.5">{{ $order->name }}</div>
                        </div>

                        @if($order->description)
                            <div>
                                <span class="text-[11px] uppercase font-bold text-gray-400">Spesifikasi & Rincian</span>
                                <div class="text-xs text-gray-700 mt-1 p-3.5 bg-gray-50 rounded-xl whitespace-pre-line border border-gray-100 leading-relaxed">
                                    {{ $order->description }}
                                </div>
                            </div>
                        @endif

                        @if($order->has_size_breakdown)
                            <div class="p-3.5 bg-indigo-50/40 rounded-xl border border-indigo-100/70">
                                <span class="text-[11px] uppercase font-bold text-indigo-900 block mb-2">Rincian Ukuran / Size Breakdown</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($order->size_breakdown as $size => $qty)
                                        @if($qty > 0)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white border border-indigo-200 text-xs font-bold text-indigo-900 shadow-2xs">
                                                <span class="text-indigo-600 font-semibold">{{ $size }}:</span>
                                                <span>{{ $qty }} pcs</span>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-{{ auth()->user()->canViewFinances() ? '3' : '1' }} gap-3 pt-2">
                            <div class="p-3 bg-gray-50/75 rounded-xl border border-gray-100">
                                <span class="text-[10px] uppercase font-bold text-gray-400">Total Jumlah</span>
                                <div class="text-sm font-bold text-gray-900 mt-0.5">{{ $order->quantity }} pcs</div>
                            </div>

                            @if(auth()->user()->canViewFinances())
                                <div class="p-3 bg-gray-50/75 rounded-xl border border-gray-100">
                                    <span class="text-[10px] uppercase font-bold text-gray-400">Harga Satuan</span>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">Rp{{ number_format($order->price_per_unit, 0, ',', '.') }}</div>
                                </div>

                                <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                                    <span class="text-[10px] uppercase font-bold text-indigo-600">Total Biaya</span>
                                    <div class="text-sm font-black text-indigo-700 mt-0.5">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                            <div>
                                <span class="text-[11px] uppercase font-bold text-gray-400">Target Selesai (Deadline)</span>
                                <div class="text-xs font-semibold text-gray-900 mt-1 flex items-center">
                                    @if($order->deadline)
                                        <svg class="w-3.5 h-3.5 me-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $order->deadline->translatedFormat('d F Y') }}
                                        @if($order->is_overdue)
                                            <span class="ms-2 px-1.5 py-0.5 bg-red-100 text-red-800 text-[10px] rounded font-bold">Terlambat</span>
                                        @elseif($order->is_due_today)
                                            <span class="ms-2 px-1.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] rounded font-bold">Hari ini</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Tidak ditentukan</span>
                                    @endif
                                </div>
                            </div>

                            @if($order->notes)
                                <div>
                                    <span class="text-[11px] uppercase font-bold text-gray-400">Catatan Khusus</span>
                                    <div class="text-xs text-gray-700 mt-1">{{ $order->notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payments Section -->
                @if(auth()->user()->canViewFinances())
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Catatan Pembayaran & DP</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola setoran uang muka dan pelunasan bertahap.</p>
                        </div>
                        <div class="text-end">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Sisa Tagihan</span>
                            <div class="text-base font-black {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ $order->remaining_amount > 0 ? 'Rp' . number_format($order->remaining_amount, 0, ',', '.') : 'LUNAS ✓' }}
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Payment Summary Box -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 bg-gray-50 rounded-xl text-center border border-gray-100">
                            <div>
                                <div class="text-[10px] font-bold uppercase text-gray-400">Total Biaya</div>
                                <div class="text-xs sm:text-sm font-bold text-gray-900 mt-0.5">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase text-gray-400">Kas Masuk Bersih</div>
                                <div class="text-xs sm:text-sm font-bold text-emerald-600 mt-0.5">Rp{{ number_format($order->total_paid, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase text-gray-400">Sisa Tagihan</div>
                                <div class="text-xs sm:text-sm font-bold {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400' }} mt-0.5">
                                    Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase text-gray-400">Total Refund</div>
                                <div class="text-xs sm:text-sm font-bold {{ $order->total_refunded > 0 ? 'text-rose-600' : 'text-gray-400' }} mt-0.5">
                                    {{ $order->total_refunded > 0 ? '-Rp' . number_format($order->total_refunded, 0, ',', '.') : 'Rp0' }}
                                </div>
                            </div>
                        </div>

                        <!-- Add Payment or Refund Tabbed Interface -->
                        <div x-data="{
                            openForm: false,
                            activeTab: '{{ $order->remaining_amount > 0 ? 'payment' : 'refund' }}',
                            // Calculator state for cash
                            payAmount: {{ (int)$order->remaining_amount }},
                            payMethod: 'cash',
                            cashGiven: 0,
                            get cashChange() {
                                const given = parseFloat(this.cashGiven) || 0;
                                const amt = parseFloat(this.payAmount) || 0;
                                return Math.max(0, given - amt);
                            },
                            get cashShortage() {
                                const given = parseFloat(this.cashGiven) || 0;
                                const amt = parseFloat(this.payAmount) || 0;
                                return Math.max(0, amt - given);
                            },
                            formatIdr(num) {
                                return new Intl.NumberFormat('id-ID').format(num);
                            }
                        }" class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-indigo-950">Transaksi Keuangan</span>
                                    @if($order->remaining_amount > 0)
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full">Sisa Tagihan</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">Lunas</span>
                                    @endif
                                </div>
                                <button @click="openForm = !openForm" type="button" class="text-xs font-semibold text-indigo-600 hover:underline">
                                    <span x-show="!openForm">+ Catat Pembayaran / Refund</span>
                                    <span x-show="openForm">Tutup Formulir</span>
                                </button>
                            </div>

                            <div x-show="openForm" class="mt-4 pt-3 border-t border-indigo-100 space-y-4">
                                <!-- Tab Switcher -->
                                <div class="flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                                    <button type="button" @click="activeTab = 'payment'"
                                            :class="activeTab === 'payment' ? 'bg-white text-indigo-700 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                                            class="flex-1 py-1.5 text-xs rounded-lg transition text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        <span>Catat Pembayaran Masuk</span>
                                    </button>
                                    <button type="button" @click="activeTab = 'refund'"
                                            :class="activeTab === 'refund' ? 'bg-white text-rose-700 shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                                            class="flex-1 py-1.5 text-xs rounded-lg transition text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        <span>Catat Pengembalian / Refund</span>
                                    </button>
                                </div>

                                <!-- FORM PEMBAYARAN MASUK -->
                                <div x-show="activeTab === 'payment'">
                                    @if($order->remaining_amount <= 0)
                                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>Pesanan ini sudah lunas. Jika ada kelebihan atau pembatalan, silakan pilih tab <strong>Catat Pengembalian / Refund</strong>.</span>
                                        </div>
                                    @else
                                        <form action="{{ route('orders.payments.store', $order) }}" method="POST" class="space-y-3.5">
                                            @csrf
                                            <input type="hidden" name="type" value="payment">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Bayar (Rp) <span class="text-red-500">*</span></label>
                                                    <input 
                                                        type="number" 
                                                        name="amount" 
                                                        x-model="payAmount"
                                                        max="{{ $order->remaining_amount }}" 
                                                        required 
                                                        class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold"
                                                    >
                                                    <p class="text-[10px] text-gray-400 mt-1">Maks sisa: Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                                    <input 
                                                        type="date" 
                                                        name="payment_date" 
                                                        value="{{ date('Y-m-d') }}" 
                                                        required 
                                                        class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode <span class="text-red-500">*</span></label>
                                                    <select name="method" x-model="payMethod" required class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                                        <option value="cash">Cash / Tunai</option>
                                                        <option value="transfer">Transfer Bank</option>
                                                        <option value="qris">QRIS</option>
                                                        <option value="other">Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Kalkulator Uang Kembalian Kasir (Khusus Cash) -->
                                            <div x-show="payMethod === 'cash'" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs space-y-2.5">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                        Kalkulator Kembalian Kasir (Tunai)
                                                    </span>
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" @click="cashGiven = payAmount" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-[10px] font-semibold text-gray-600 rounded">Uang Pas</button>
                                                        <button type="button" @click="cashGiven = Math.ceil(payAmount / 50000) * 50000" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-[10px] font-semibold text-gray-600 rounded">Pecahan 50rb</button>
                                                        <button type="button" @click="cashGiven = Math.ceil(payAmount / 100000) * 100000" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-[10px] font-semibold text-gray-600 rounded">Pecahan 100rb</button>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                                                    <div>
                                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Uang Diterima dari Pelanggan (Rp)</label>
                                                        <input type="number" x-model="cashGiven" placeholder="Contoh: 100000"
                                                               class="w-full text-xs rounded-lg border-gray-300 px-3 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                                    </div>
                                                    <div class="p-2.5 rounded-lg" :class="cashGiven > 0 && cashGiven >= payAmount ? 'bg-emerald-50 border border-emerald-200' : (cashGiven > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 border border-gray-200')">
                                                        <div class="text-[10px] font-bold uppercase tracking-wider" :class="cashGiven > 0 && cashGiven >= payAmount ? 'text-emerald-700' : 'text-gray-500'">
                                                            <span x-show="cashGiven >= payAmount">Kembalian ke Pelanggan</span>
                                                            <span x-show="cashGiven < payAmount">Kurang Bayar</span>
                                                        </div>
                                                        <div class="text-sm font-black mt-0.5" :class="cashGiven > 0 && cashGiven >= payAmount ? 'text-emerald-700' : (cashGiven > 0 ? 'text-amber-700' : 'text-gray-700')">
                                                            <span x-text="'Rp ' + (cashGiven >= payAmount ? formatIdr(cashChange) : formatIdr(cashShortage))"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Pembayaran (Opsional)</label>
                                                <input 
                                                    type="text" 
                                                    name="notes" 
                                                    placeholder="Contoh: Transfer BCA / Pelunasan tunai saat ambil barang" 
                                                    class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                >
                                            </div>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                    Simpan Pembayaran & Siapkan WA
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                <!-- FORM PENGEMBALIAN DANA (REFUND) -->
                                <div x-show="activeTab === 'refund'">
                                    @if($order->total_paid <= 0)
                                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            <span>Pesanan ini belum memiliki riwayat pembayaran masuk, sehingga belum dapat melakukan pengembalian dana (refund).</span>
                                        </div>
                                    @else
                                        <form action="{{ route('orders.payments.store', $order) }}" method="POST" class="space-y-3.5">
                                            @csrf
                                            <input type="hidden" name="type" value="refund">
                                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed">
                                                <strong>Catatan Kasir:</strong> Pengembalian dana akan mengurangi saldo total kas masuk pesanan ini dan dicatat di laporan arus kas. Uang tunai/transfer akan dikeluarkan dari toko kembali ke pelanggan.
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Refund (Rp) <span class="text-red-500">*</span></label>
                                                    <input 
                                                        type="number" 
                                                        name="amount" 
                                                        max="{{ $order->total_paid }}" 
                                                        value="{{ old('amount', $order->total_paid) }}" 
                                                        required 
                                                        class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-rose-500 focus:border-rose-500 font-semibold text-rose-700"
                                                    >
                                                    <p class="text-[10px] text-gray-400 mt-1">Maksimal: Rp{{ number_format($order->total_paid, 0, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Refund <span class="text-red-500">*</span></label>
                                                    <input 
                                                        type="date" 
                                                        name="payment_date" 
                                                        value="{{ date('Y-m-d') }}" 
                                                        required 
                                                        class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-rose-500 focus:border-rose-500"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pengembalian <span class="text-red-500">*</span></label>
                                                    <select name="method" required class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-rose-500 focus:border-rose-500 font-medium">
                                                        <option value="cash">Cash (Uang Laci Kasir)</option>
                                                        <option value="transfer">Transfer Bank</option>
                                                        <option value="other">Lainnya</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pengembalian (Refund) <span class="text-red-500">*</span></label>
                                                <input 
                                                    type="text" 
                                                    name="notes" 
                                                    required
                                                    placeholder="Contoh: Batal pesanan karena bahan habis / Retur 5 pcs cacat sablon" 
                                                    class="w-full text-xs rounded-xl border-gray-300 px-3 py-2 focus:ring-rose-500 focus:border-rose-500"
                                                >
                                            </div>
                                            <div class="flex justify-end pt-1">
                                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                    Simpan Pengembalian Dana & Siapkan WA
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Payments History Table -->
                        @if($order->payments->isEmpty())
                            <div class="text-center py-4 text-xs text-gray-400">
                                Belum ada riwayat transaksi pembayaran/pengembalian untuk pesanan ini.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-gray-600">
                                    <thead class="bg-gray-50 uppercase text-[10px] text-gray-400 font-bold border-b border-gray-100">
                                        <tr>
                                            <th class="py-2.5 px-3">Tanggal</th>
                                            <th class="py-2.5 px-3">Tipe</th>
                                            <th class="py-2.5 px-3">Jumlah</th>
                                            <th class="py-2.5 px-3">Metode</th>
                                            <th class="py-2.5 px-3">Catatan / Alasan</th>
                                            <th class="py-2.5 px-3 text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($order->payments as $payment)
                                            <tr class="{{ $payment->isRefund() ? 'bg-rose-50/40' : '' }}">
                                                <td class="py-2.5 px-3 font-medium text-gray-800">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                <td class="py-2.5 px-3">
                                                    @if($payment->isRefund())
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                            Refund Keluar
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                            Masuk
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-bold {{ $payment->isRefund() ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $payment->isRefund() ? '-Rp' : '+Rp' }}{{ number_format($payment->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="px-2 py-0.5 {{ $payment->isRefund() ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-700' }} rounded text-[11px] font-medium">
                                                        {{ $payment->method_label }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-gray-600 {{ $payment->isRefund() ? 'italic font-medium text-rose-900' : '' }}">
                                                    {{ $payment->notes ?: '-' }}
                                                </td>
                                                <td class="py-2.5 px-3 text-end">
                                                    <form action="{{ route('orders.payments.destroy', [$order, $payment]) }}" method="POST" onsubmit="return confirm('Hapus catatan {{ $payment->isRefund() ? 'pengembalian dana' : 'pembayaran' }} ini?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-[11px]">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Design Files Section -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-900">Berkas Desain & Mockup</h2>
                        <p class="text-xs text-gray-500 mt-0.5">File mockup, master layout CDR/AI/PDF/PNG untuk pesanan ini.</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4">
                        <!-- Upload Form -->
                        <form action="{{ route('orders.files.store', $order) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2.5">
                            @csrf
                            <input 
                                type="file" 
                                name="files[]" 
                                multiple 
                                required 
                                accept=".jpg,.jpeg,.png,.pdf,.zip,.rar,.ai,.psd" 
                                class="w-full text-xs text-gray-500 file:me-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-300 rounded-xl cursor-pointer"
                            >
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white font-semibold text-xs rounded-xl shadow-xs shrink-0 transition">
                                Upload File
                            </button>
                        </form>

                        <!-- Files List -->
                        @if($order->files->isEmpty())
                            <div class="text-center py-4 text-xs text-gray-400">
                                Belum ada berkas desain yang dilampirkan.
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($order->files as $file)
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5 overflow-hidden">
                                            <span class="text-lg">{{ $file->icon }}</span>
                                            <div class="truncate">
                                                <a href="{{ route('orders.files.download', [$order, $file]) }}" class="font-bold text-xs text-gray-800 hover:text-indigo-600 truncate block">
                                                    {{ $file->file_name }}
                                                </a>
                                                <span class="text-[10px] text-gray-400">{{ $file->file_size_formatted }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <a href="{{ route('orders.files.download', [$order, $file]) }}" title="Download File" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('orders.files.destroy', [$order, $file]) }}" method="POST" onsubmit="return confirm('Hapus file ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus File" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Customer Quick Info & WhatsApp Shortcuts -->
            <div class="space-y-6">

                <!-- Customer Card -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-xs uppercase font-bold text-gray-400">Data Pelanggan</span>
                        <a href="{{ route('customers.show', $order->customer) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                            Profil →
                        </a>
                    </div>

                    <div class="p-5 space-y-3.5">
                        <div>
                            <div class="text-sm font-bold text-gray-900">{{ $order->customer->name }}</div>
                            @if($order->customer->phone)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $order->customer->phone }}</div>
                            @endif
                        </div>

                        @if($order->customer->address)
                            <div class="text-xs text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100 leading-relaxed">
                                <span class="font-bold text-gray-700">Alamat:</span> {{ $order->customer->address }}
                            </div>
                        @endif

                        @if($order->customer->phone)
                            <a href="https://wa.me/{{ $order->customer->whats_app_number }}" target="_blank" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                Buka Chat WhatsApp
                            </a>
                        @endif
                    </div>
                </div>

                <!-- WhatsApp Quick Message Templates -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Template Pesan WhatsApp</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Satu klik untuk kirim pesan tanpa mengetik ulang.</p>
                    </div>

                    <div class="p-5 space-y-2.5">
                        <!-- 1. Update Status -->
                        <a href="{{ $waStatusUrl }}" target="_blank" class="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-gray-200 text-xs font-semibold text-gray-800 hover:text-emerald-800 transition">
                            <span class="flex items-center gap-2">
                                <span>📦</span>
                                <span>Update Status & Deadline</span>
                            </span>
                            <span class="text-gray-400">↗</span>
                        </a>

                        <!-- 2. Pesanan Selesai -->
                        <a href="{{ $waCompletedUrl }}" target="_blank" class="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-blue-50 hover:border-blue-200 border border-gray-200 text-xs font-semibold text-gray-800 hover:text-blue-800 transition">
                            <span class="flex items-center gap-2">
                                <span>🎉</span>
                                <span>Pesanan Sudah Selesai</span>
                            </span>
                            <span class="text-gray-400">↗</span>
                        </a>
                    </div>
                </div>

                <!-- Public Order Tracking Link -->
                @if($order->tracking_url)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden" x-data="{ copied: false }">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Link Lacak Pelanggan</h3>
                            <p class="text-[11px] text-gray-400 mt-0.5">Bisa dibuka customer tanpa login untuk cek status.</p>
                        </div>

                        <div class="p-5 space-y-3">
                            <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-mono text-gray-600 break-all select-all">
                                {{ $order->tracking_url }}
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ $order->tracking_url }}" target="_blank"
                                   class="flex-1 text-center py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                                    Buka Halaman ↗
                                </a>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $order->tracking_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex-1 py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-xl border border-indigo-200 transition">
                                    <span x-show="!copied">Salin Link</span>
                                    <span x-show="copied" class="text-emerald-700 font-bold">✓ Tersalin!</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-app-layout>
