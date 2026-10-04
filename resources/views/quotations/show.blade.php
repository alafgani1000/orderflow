<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('quotations.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold text-gray-900">{{ __('Penawaran #:number', ['number' => $quotation->quotation_number]) }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $quotation->status_badge_class }}">
                            {{ $quotation->status_label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Dibuat pada :date', ['date' => $quotation->created_at->translatedFormat('d F Y - H:i')]) }}
                        @if($quotation->valid_until)
                            • {{ __('Berlaku hingga :date', ['date' => $quotation->valid_until->translatedFormat('d F Y')]) }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2" x-data="{
                copied: false,
                copyLink(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    });
                }
            }">
                <!-- Kirim WA -->
                <a href="{{ $waUrl }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>{{ __('Kirim Penawaran WA') }}</span>
                </a>

                <!-- Salin Tautan Publik Pelanggan -->
                <button type="button" @click="copyLink(@js($quotation->public_url))"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span x-text="copied ? @js(__('Tautan Tersalin! ✓')) : @js(__('Salin Link Publik'))"></span>
                </button>

                <!-- Buka Tautan Publik -->
                <a href="{{ $quotation->public_url }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>{{ __('Preview Pelanggan') }}</span>
                </a>

                @if(!$quotation->isConverted())
                    <a href="{{ route('quotations.edit', $quotation) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('Edit') }}</span>
                    </a>
                @endif

                @if($quotation->canBeConverted())
                    <form action="{{ route('quotations.convert', $quotation) }}" method="POST" class="inline"
                          onsubmit="return confirm(@js(__('Konversi penawaran ini menjadi pesanan produksi sekarang?')));">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Konversi ke Pesanan') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Status Alert Banners -->
        @if($quotation->isConverted() && $quotation->order)
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-indigo-950">{{ __('Penawaran ini telah dikonversi menjadi pesanan produksi!') }}</h4>
                        <p class="text-[11px] text-indigo-700 mt-0.5">{{ __('Nomor Pesanan:') }} <strong class="font-mono">#{{ $quotation->order->order_number }}</strong></p>
                    </div>
                </div>
                <a href="{{ route('orders.show', $quotation->order) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition shrink-0">
                    {{ __('Buka Pesanan') }} &rarr;
                </a>
            </div>
        @elseif($quotation->isApproved())
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-950">{{ __('Pelanggan telah menyetujui penawaran ini!') }}</h4>
                        <p class="text-[11px] text-emerald-700 mt-0.5">
                            {{ __('Disetujui oleh :name pada :date', [
                                'name' => $quotation->approved_by_name ?? $quotation->customer->name,
                                'date' => $quotation->approved_at ? $quotation->approved_at->translatedFormat('d F Y H:i') : '-'
                            ]) }}
                        </p>
                    </div>
                </div>
                @if($quotation->canBeConverted())
                    <form action="{{ route('quotations.convert', $quotation) }}" method="POST" class="inline"
                          onsubmit="return confirm(@js(__('Konversi penawaran ini menjadi pesanan produksi sekarang?')));">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl transition shrink-0">
                            {{ __('Konversi Sekarang') }} &rarr;
                        </button>
                    </form>
                @endif
            </div>
        @elseif($quotation->isRejected())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-rose-950">{{ __('Pelanggan menolak penawaran ini') }}</h4>
                    <p class="text-[11px] text-rose-800 mt-1">
                        <strong>{{ __('Alasan Penolakan:') }}</strong> {{ $quotation->rejection_reason ?: __('Tidak disebutkan.') }}
                    </p>
                </div>
            </div>
        @elseif($quotation->isExpired())
            <div class="p-4 bg-gray-100 border border-gray-300 rounded-2xl flex items-center gap-3 text-gray-700 text-xs">
                <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('Masa berlaku penawaran ini telah berakhir (:date). Anda dapat memperbarui masa berlaku melalui menu Edit.', ['date' => $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-']) }}</span>
            </div>
        @endif

        <!-- Card Dokumen Penawaran Resmi -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-2xs space-y-6 print:p-0 print:border-none">
            <!-- Header Dokumen -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-gray-100 pb-6">
                <div>
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ auth()->user()->business_name ?: auth()->user()->name }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->business_address ?: __('Workshop Sablon & Percetakan') }}</p>
                    @if(auth()->user()->phone)
                        <p class="text-xs text-gray-500">{{ __('WhatsApp/Telp:') }} {{ auth()->user()->phone }}</p>
                    @endif
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ __('SURAT PENAWARAN HARGA') }}</span>
                    <h3 class="text-lg font-mono font-black text-gray-900 mt-0.5">{{ $quotation->quotation_number }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Tanggal:') }} {{ $quotation->created_at->format('d/m/Y') }}</p>
                    @if($quotation->valid_until)
                        <p class="text-xs text-amber-600 font-semibold">{{ __('Berlaku s/d:') }} {{ $quotation->valid_until->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>

            <!-- Ditujukan Kepada & Judul -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50/70 rounded-xl border border-gray-200 text-xs">
                <div>
                    <span class="text-gray-400 uppercase font-bold text-[10px] tracking-wider">{{ __('Ditujukan Kepada:') }}</span>
                    <h4 class="font-bold text-gray-900 text-sm mt-0.5">{{ $quotation->customer->name }}</h4>
                    @if($quotation->customer->phone)
                        <p class="text-gray-600 mt-0.5">{{ $quotation->customer->phone }}</p>
                    @endif
                    @if($quotation->customer->address)
                        <p class="text-gray-500 mt-0.5">{{ $quotation->customer->address }}</p>
                    @endif
                </div>
                <div>
                    <span class="text-gray-400 uppercase font-bold text-[10px] tracking-wider">{{ __('Perihal / Judul Penawaran:') }}</span>
                    <p class="font-bold text-gray-900 text-sm mt-0.5">{{ $quotation->title }}</p>
                </div>
            </div>

            <!-- Tabel Rincian Item Penawaran -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-gray-200 rounded-xl overflow-hidden">
                    <thead class="bg-gray-100/70 text-gray-700 font-bold uppercase text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">{{ __('Item & Deskripsi') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Jumlah') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Harga Satuan') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Total Harga') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quotation->items as $idx => $item)
                            <tr>
                                <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900">{{ $item->item_name }}</div>
                                    @if($item->description)
                                        <div class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-gray-800">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-gray-700">
                                    Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900">
                                    Rp{{ number_format($item->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/80 font-medium text-xs">
                        <tr>
                            <td colspan="4" class="py-2.5 px-4 text-right text-gray-600">{{ __('Subtotal:') }}</td>
                            <td class="py-2.5 px-4 text-right font-bold text-gray-900">Rp{{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($quotation->discount > 0)
                            <tr>
                                <td colspan="4" class="py-2 px-4 text-right text-rose-600">{{ __('Potongan Diskon:') }}</td>
                                <td class="py-2 px-4 text-right font-bold text-rose-600">-Rp{{ number_format($quotation->discount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if($quotation->tax > 0)
                            <tr>
                                <td colspan="4" class="py-2 px-4 text-right text-gray-600">{{ __('Pajak / PPN:') }}</td>
                                <td class="py-2 px-4 text-right font-bold text-gray-900">+Rp{{ number_format($quotation->tax, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="border-t-2 border-gray-300 text-sm">
                            <td colspan="4" class="py-3 px-4 text-right font-black text-gray-900 uppercase">{{ __('Total Biaya Penawaran:') }}</td>
                            <td class="py-3 px-4 text-right font-black text-indigo-700 text-base">Rp{{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Catatan & Ketentuan -->
            @if($quotation->notes)
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                    <h4 class="font-bold text-gray-800 uppercase text-[10px] tracking-wider">{{ __('Catatan & Ketentuan:') }}</h4>
                    <p class="text-gray-600 whitespace-pre-line leading-relaxed">{{ $quotation->notes }}</p>
                </div>
            @endif

            <!-- Rekening Pembayaran Toko -->
            @if(auth()->user()->business_bank_name && auth()->user()->business_bank_account)
                <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 text-xs">
                    <span class="font-bold text-indigo-900 uppercase text-[10px] tracking-wider">{{ __('Informasi Rekening Pembayaran DP / Pelunasan:') }}</span>
                    <p class="font-bold text-gray-900 mt-1">
                        {{ auth()->user()->business_bank_name }} - {{ auth()->user()->business_bank_account }} (a.n. {{ auth()->user()->business_bank_holder }})
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
