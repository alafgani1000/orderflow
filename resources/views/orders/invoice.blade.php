<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->order_number }} — {{ $order->customer->name }}</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .invoice-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 12mm;
            }
        }
    </style>
</head>
<body x-data="{ docMode: 'invoice' }" class="bg-gray-100 min-h-screen text-gray-800 antialiased p-4 sm:p-8 font-sans">

    <!-- Action Toolbar (hidden during print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.show', $order) }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <span class="text-xs text-gray-400 font-bold uppercase" x-text="docMode === 'invoice' ? 'Nota Invoice' : 'Surat Jalan & SPK'"></span>
                <h2 class="text-base font-bold text-gray-900">#{{ $order->order_number }}</h2>
            </div>
        </div>

        <!-- Document Mode Toggle -->
        <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
            <button type="button" @click="docMode = 'invoice'"
                    :class="docMode === 'invoice' ? 'bg-white text-indigo-700 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                    class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Nota / Invoice</span>
            </button>
            <button type="button" @click="docMode = 'spk'"
                    :class="docMode === 'spk' ? 'bg-white text-indigo-700 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                    class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Surat Jalan &amp; SPK</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($order->customer->phone)
                <a href="{{ app(\App\Services\WhatsAppService::class)->statusUrl($order) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>Kirim via WA</span>
                </a>
            @endif

            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Main Sheet Container -->
    <div class="invoice-card max-w-4xl mx-auto bg-white rounded-3xl border border-gray-200 shadow-md p-6 sm:p-12 space-y-8">

        <!-- Header: Business Info & Document Title -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b-2 border-gray-100">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-sm">
                        OF
                    </div>
                    <span class="font-extrabold text-xl tracking-tight text-gray-900">
                        {{ $order->user->business_name ?: 'OrderFlow Workshop' }}
                    </span>
                </div>
                <div class="text-xs text-gray-500 space-y-0.5">
                    <p class="font-semibold text-gray-700">{{ $order->user->name }}</p>
                    @if($order->user->phone)
                        <p>WhatsApp: {{ $order->user->phone }}</p>
                    @endif
                    <p>{{ $order->user->email }}</p>
                </div>
            </div>

            <div class="sm:text-right space-y-1">
                <span class="text-xs font-bold uppercase tracking-widest px-2.5 py-1 rounded-md"
                      :class="docMode === 'invoice' ? 'text-indigo-600 bg-indigo-50' : 'text-purple-700 bg-purple-50 border border-purple-200'"
                      x-text="docMode === 'invoice' ? 'NOTA / INVOICE PESANAN' : 'SURAT JALAN & SPK PRODUKSI'">
                </span>
                <h1 class="text-2xl font-black text-gray-900 mt-1">#{{ $order->order_number }}</h1>
                <div class="text-xs text-gray-500 pt-1">
                    <p><span class="font-medium text-gray-400">Tanggal Pesan:</span> {{ $order->created_at->translatedFormat('d F Y') }}</p>
                    @if($order->deadline)
                        <p><span class="font-medium text-gray-400">Target Selesai:</span> {{ $order->deadline->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4 rounded-2xl bg-gray-50/70 border border-gray-100">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">
                    <span x-show="docMode === 'invoice'">Ditujukan Kepada</span>
                    <span x-show="docMode === 'spk'">Tujuan Pengiriman / Pemesan</span>
                </span>
                <h3 class="text-base font-bold text-gray-900">{{ $order->customer->name }}</h3>
                @if($order->customer->phone)
                    <p class="text-xs text-gray-600 mt-0.5">WhatsApp: <span class="font-semibold text-gray-800">{{ $order->customer->phone }}</span></p>
                @endif
                @if($order->customer->address)
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed"><span class="font-medium text-gray-700">Alamat:</span> {{ $order->customer->address }}</p>
                @endif
            </div>

            <div class="sm:text-right flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Tahap Pengerjaan</span>
                    <x-status-badge :status="$order->status" class="text-xs font-bold" />
                </div>
                <!-- Status Tagihan (Hanya di mode invoice) -->
                <div x-show="docMode === 'invoice'" class="mt-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-0.5">Status Tagihan</span>
                    @if($order->is_paid_off)
                        <span class="inline-flex items-center gap-1 text-emerald-700 font-black text-sm bg-emerald-100/60 px-2.5 py-0.5 rounded-md border border-emerald-300">
                            ✓ LUNAS
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-amber-700 font-bold text-xs bg-amber-100/60 px-2.5 py-0.5 rounded-md border border-amber-300">
                            BELUM LUNAS (Sisa DP)
                        </span>
                    @endif
                </div>
                <!-- Petunjuk QC (Hanya di mode SPK) -->
                <div x-show="docMode === 'spk'" class="mt-2 text-xs text-indigo-700 font-semibold bg-indigo-50/60 px-3 py-1.5 rounded-xl border border-indigo-100 inline-block">
                    ⚠️ Harap periksa spesifikasi &amp; QC sebelum serah terima barang
                </div>
            </div>
        </div>

        <!-- ======================= MODE 1: NOTA / INVOICE ======================= -->
        <div x-show="docMode === 'invoice'" class="space-y-8">
            <!-- Order Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-[11px] uppercase font-bold text-gray-400">
                            <th class="py-3 px-2 w-12 text-center">No</th>
                            <th class="py-3 px-3">Deskripsi Item &amp; Spesifikasi</th>
                            <th class="py-3 px-3 text-center w-24">Jumlah</th>
                            <th class="py-3 px-3 text-right w-36">Harga Satuan</th>
                            <th class="py-3 px-3 text-right w-40">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-4 px-2 text-center font-bold text-gray-400">1</td>
                            <td class="py-4 px-3">
                                <div class="font-bold text-gray-900 text-sm">{{ $order->name }}</div>
                                @if($order->description)
                                    <div class="text-xs text-gray-500 mt-1 whitespace-pre-line leading-relaxed">{{ $order->description }}</div>
                                @endif
                                @if($order->has_size_breakdown)
                                    <div class="mt-2 text-xs font-semibold text-gray-700 bg-gray-50 p-2 rounded-lg border border-gray-200 inline-block">
                                        <span class="text-gray-500 font-bold uppercase text-[10px] block mb-0.5">Rincian Ukuran:</span>
                                        {{ $order->size_summary }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-center font-semibold text-gray-800">
                                {{ $order->quantity }} pcs
                            </td>
                            <td class="py-4 px-3 text-right font-medium text-gray-800 whitespace-nowrap">
                                Rp{{ number_format($order->price_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-3 text-right font-bold text-gray-900 whitespace-nowrap">
                                Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Payment History & Total Calculation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-100">
                <!-- Left: Payment History -->
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block mb-2">Riwayat Pembayaran &amp; Refund</span>
                    @if($order->payments->isEmpty())
                        <p class="text-xs text-gray-400 italic">Belum ada setoran pembayaran yang dicatat.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400 border-b border-gray-200">
                                    <tr>
                                        <th class="py-2 px-2.5">Tanggal</th>
                                        <th class="py-2 px-2.5">Metode</th>
                                        <th class="py-2 px-2.5 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($order->payments as $payment)
                                        <tr class="{{ $payment->isRefund() ? 'bg-rose-50/50' : '' }}">
                                            <td class="py-2 px-2.5 text-gray-700">
                                                {{ $payment->payment_date->format('d/m/Y') }}
                                                @if($payment->isRefund())
                                                    <span class="text-[9px] font-bold text-rose-600 block">(Refund)</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-2.5 text-gray-500">{{ $payment->method_label }}</td>
                                            <td class="py-2 px-2.5 text-right font-semibold {{ $payment->isRefund() ? 'text-rose-600' : 'text-emerald-600' }}">
                                                {{ $payment->isRefund() ? '-Rp' : 'Rp' }}{{ number_format($payment->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($order->notes)
                        <div class="mt-4 p-3 bg-amber-50/50 rounded-xl border border-amber-100 text-xs">
                            <span class="font-bold text-amber-900 block mb-0.5">Catatan Khusus:</span>
                            <p class="text-amber-800 leading-relaxed">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Right: Totals Breakdown -->
                <div class="space-y-2 text-sm sm:max-w-sm sm:ms-auto w-full">
                    <div class="flex justify-between py-1.5 text-gray-600">
                        <span>Total Tagihan:</span>
                        <span class="font-bold text-gray-900">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 text-emerald-700">
                        <span>Total Kas Masuk Bersih:</span>
                        <span class="font-bold">(-) Rp{{ number_format($order->total_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-t-2 border-b-2 border-gray-900 text-base">
                        <span class="font-black text-gray-900">SISA TAGIHAN:</span>
                        <span class="font-black {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $order->remaining_amount > 0 ? 'Rp' . number_format($order->remaining_amount, 0, ',', '.') : 'Rp0 (LUNAS)' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Signature Section (Invoice) -->
            <div class="grid grid-cols-2 gap-8 pt-8 text-center text-xs">
                <div class="space-y-16">
                    <p class="font-semibold text-gray-600">Tanda Terima Pemesan,</p>
                    <div class="border-t border-gray-300 w-44 mx-auto pt-1 text-gray-500">
                        {{ $order->customer->name }}
                    </div>
                </div>
                <div class="space-y-16">
                    <p class="font-semibold text-gray-600">Hormat Kami,</p>
                    <div class="border-t border-gray-300 w-44 mx-auto pt-1 text-gray-800 font-bold">
                        {{ $order->user->business_name ?: $order->user->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= MODE 2: SURAT JALAN & SPK WORKSHOP ======================= -->
        <div x-show="docMode === 'spk'" class="space-y-8">
            <!-- Order Specs & Production Breakdown (No Financial Margins) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-[11px] uppercase font-bold text-gray-500 bg-gray-50/50">
                            <th class="py-3 px-2 w-10 text-center">No</th>
                            <th class="py-3 px-3">Item Pekerjaan &amp; Spesifikasi Workshop</th>
                            <th class="py-3 px-3 w-48">Rincian Ukuran (Size)</th>
                            <th class="py-3 px-3 text-center w-24">Jumlah</th>
                            <th class="py-3 px-3 w-44">Checklist QC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-4 px-2 text-center font-bold text-gray-400">1</td>
                            <td class="py-4 px-3">
                                <div class="font-bold text-gray-900 text-base">{{ $order->name }}</div>
                                @if($order->description)
                                    <div class="text-xs text-gray-700 mt-2 p-2.5 rounded-xl bg-gray-50 border border-gray-200 whitespace-pre-line leading-relaxed">
                                        <span class="font-bold text-gray-900 block mb-1 text-[11px]">Instruksi Teknis / Desain:</span>
                                        {{ $order->description }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-3 align-top">
                                @if($order->has_size_breakdown)
                                    <div class="space-y-1">
                                        @foreach($order->size_breakdown as $size => $qty)
                                            @if($qty > 0)
                                                <div class="flex items-center justify-between px-2.5 py-1 bg-indigo-50/80 border border-indigo-200/80 rounded-lg text-xs font-bold text-indigo-900">
                                                    <span>{{ $size }}:</span>
                                                    <span>{{ $qty }} pcs</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">All Size / Standar</span>
                                @endif
                            </td>
                            <td class="py-4 px-3 text-center align-top">
                                <span class="inline-block px-3 py-1 bg-gray-100 border border-gray-300 rounded-lg font-black text-sm text-gray-900">
                                    {{ $order->quantity }} pcs
                                </span>
                            </td>
                            <td class="py-4 px-3 text-xs text-gray-600 space-y-1.5 align-top">
                                <label class="flex items-center gap-1.5">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                                    <span>Bahan &amp; Cutting</span>
                                </label>
                                <label class="flex items-center gap-1.5">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                                    <span>Sablon / Cetak</span>
                                </label>
                                <label class="flex items-center gap-1.5">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                                    <span>Finishing &amp; Jahit</span>
                                </label>
                                <label class="flex items-center gap-1.5">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                                    <span>QC &amp; Packing</span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($order->notes)
                <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200 text-xs">
                    <span class="font-bold text-amber-900 block mb-1">Catatan Pengiriman / Serah Terima:</span>
                    <p class="text-amber-800 leading-relaxed">{{ $order->notes }}</p>
                </div>
            @endif

            <!-- 3 Signatures for Surat Jalan & SPK -->
            <div class="grid grid-cols-3 gap-6 pt-8 text-center text-xs border-t border-gray-200">
                <div class="space-y-16">
                    <p class="font-semibold text-gray-700">Penerima Barang,</p>
                    <div class="border-t border-gray-300 w-36 mx-auto pt-1 text-gray-500">
                        {{ $order->customer->name }}
                    </div>
                </div>
                <div class="space-y-16">
                    <p class="font-semibold text-gray-700">Pengirim / Driver,</p>
                    <div class="border-t border-gray-300 w-36 mx-auto pt-1 text-gray-500">
                        ( ................................ )
                    </div>
                </div>
                <div class="space-y-16">
                    <p class="font-semibold text-gray-700">Penanggung Jawab Produksi,</p>
                    <div class="border-t border-gray-300 w-36 mx-auto pt-1 text-gray-800 font-bold">
                        {{ $order->user->business_name ?: $order->user->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="pt-6 border-t border-gray-100 text-center text-[10px] text-gray-400">
            Dokumen sah ini digenerate secara otomatis oleh sistem manajemen pesanan OrderFlow.
        </div>
    </div>

</body>
</html>
