<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Surat Penawaran Harga') }} #{{ $quotation->quotation_number }} - {{ $quotation->user->business_name ?: $quotation->user->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-900 antialiased p-4 sm:p-8">
    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        showApproveModal: false,
        showRejectModal: false
    }">
        <!-- Session Flash Messages -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-900 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-300 text-rose-900 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Dokumen Penawaran -->
        <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-10 shadow-sm space-y-8">
            <!-- Header Toko & Dokumen -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-gray-100 pb-6">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-indigo-600">{{ __('PENAWARAN RESMI') }}</span>
                    <h1 class="text-2xl font-black text-gray-900 mt-1 uppercase">{{ $quotation->user->business_name ?: $quotation->user->name }}</h1>
                    @if($quotation->user->business_address)
                        <p class="text-xs text-gray-500 mt-1 max-w-sm leading-relaxed">{{ $quotation->user->business_address }}</p>
                    @endif
                    @if($quotation->user->phone)
                        <p class="text-xs text-gray-600 mt-1">WhatsApp/Telp: <strong>{{ $quotation->user->phone }}</strong></p>
                    @endif
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <span class="text-xs font-mono font-bold text-gray-500">{{ $quotation->quotation_number }}</span>
                    <div class="text-xs text-gray-500">{{ __('Tanggal:') }} {{ $quotation->created_at->translatedFormat('d F Y') }}</div>
                    @if($quotation->valid_until)
                        <div class="text-xs font-semibold {{ $quotation->isExpired() ? 'text-rose-600' : 'text-amber-700' }}">
                            {{ __('Berlaku s/d:') }} {{ $quotation->valid_until->translatedFormat('d F Y') }}
                        </div>
                    @endif
                    <div class="pt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $quotation->status_badge_class }}">
                            {{ $quotation->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kepada & Perihal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Ditujukan Kepada:') }}</span>
                    <h2 class="text-sm font-bold text-gray-900 mt-0.5">{{ $quotation->customer->name }}</h2>
                    @if($quotation->customer->phone)
                        <p class="text-gray-600">{{ $quotation->customer->phone }}</p>
                    @endif
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Perihal / Judul Penawaran:') }}</span>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $quotation->title }}</p>
                </div>
            </div>

            <!-- Tabel Rincian Item -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-gray-200 rounded-2xl overflow-hidden">
                    <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">#</th>
                            <th class="py-3 px-4">{{ __('Item & Deskripsi') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Jumlah') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Harga Satuan') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quotation->items as $idx => $item)
                            <tr>
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $item->item_name }}</div>
                                    @if($item->description)
                                        <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-gray-800">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-gray-700">
                                    Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-gray-900">
                                    Rp{{ number_format($item->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-medium text-xs">
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
                                <td colspan="4" class="py-2 px-4 text-right text-gray-600">{{ __('Pajak:') }}</td>
                                <td class="py-2 px-4 text-right font-bold text-gray-900">+Rp{{ number_format($quotation->tax, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="4" class="py-4 px-4 text-right font-black text-gray-900 uppercase text-sm">{{ __('Total Penawaran:') }}</td>
                            <td class="py-4 px-4 text-right font-black text-indigo-700 text-lg">Rp{{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Catatan & Ketentuan -->
            @if($quotation->notes)
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-xs space-y-1">
                    <h3 class="font-bold text-gray-700 uppercase text-[10px] tracking-wider">{{ __('Catatan & Syarat Ketentuan Toko:') }}</h3>
                    <p class="text-gray-600 whitespace-pre-line leading-relaxed">{{ $quotation->notes }}</p>
                </div>
            @endif

            <!-- Rekening Pembayaran -->
            @if($quotation->user->business_bank_name && $quotation->user->business_bank_account)
                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 text-xs space-y-1">
                    <span class="font-bold text-indigo-900 uppercase text-[10px] tracking-wider">{{ __('Informasi Rekening Pembayaran:') }}</span>
                    <p class="font-bold text-gray-900">
                        {{ $quotation->user->business_bank_name }} - {{ $quotation->user->business_bank_account }} (a.n. {{ $quotation->user->business_bank_holder }})
                    </p>
                </div>
            @endif

            <!-- Status Banner & Aksi Pelanggan -->
            <div class="pt-4 border-t border-gray-100">
                @if($quotation->isApproved() || $quotation->isConverted())
                    <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-950">{{ __('Penawaran ini telah Anda setujui!') }}</h4>
                                <p class="text-[11px] text-emerald-800 mt-0.5">
                                    {{ __('Disetujui oleh :name pada :date', [
                                        'name' => $quotation->approved_by_name ?? $quotation->customer->name,
                                        'date' => $quotation->approved_at ? $quotation->approved_at->translatedFormat('d F Y H:i') : '-'
                                    ]) }}
                                </p>
                            </div>
                        </div>
                        @if($quotation->user->phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $quotation->user->phone) }}?text={{ urlencode('Halo ' . ($quotation->user->business_name ?: $quotation->user->name) . ', saya telah menyetujui penawaran #' . $quotation->quotation_number . '. Mohon diproses, terima kasih.') }}"
                               target="_blank"
                               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shrink-0">
                                {{ __('Chat WhatsApp Toko') }}
                            </a>
                        @endif
                    </div>
                @elseif($quotation->isRejected())
                    <div class="p-4 bg-gray-100 border border-gray-300 rounded-2xl text-xs text-gray-700">
                        <strong>{{ __('Penawaran ini telah ditolak.') }}</strong>
                        @if($quotation->rejection_reason)
                            <p class="text-gray-500 mt-1">{{ __('Alasan:') }} {{ $quotation->rejection_reason }}</p>
                        @endif
                    </div>
                @elseif($quotation->isExpired())
                    <div class="p-4 bg-gray-100 border border-gray-300 rounded-2xl text-xs text-gray-700">
                        {{ __('Masa berlaku penawaran ini telah berakhir. Silakan hubungi kami jika Anda memerlukan perpanjangan penawaran harga.') }}
                    </div>
                @else
                    <!-- Tombol Aksi Pelanggan -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-gray-500">
                            {{ __('Silakan konfirmasi persetujuan penawaran ini agar kami dapat menjadwalkan produksi.') }}
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="showRejectModal = true"
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                {{ __('Tolak Penawaran') }}
                            </button>
                            <button type="button" @click="showApproveModal = true"
                                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                {{ __('✓ Setujui Penawaran') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-400">
            Powered by OrderFlow • {{ date('Y') }}
        </div>

        <!-- Modal Setujui Penawaran -->
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="showApproveModal = false">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Konfirmasi Persetujuan Penawaran') }}</h3>
                    <button type="button" @click="showApproveModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form action="{{ route('quotations.public.approve', $quotation->public_token) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Nama Penanggung Jawab / Penyetuju') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="approved_by_name" value="{{ old('approved_by_name', $quotation->customer->name) }}" required
                               placeholder="{{ __('Contoh: ' . $quotation->customer->name) }}"
                               class="w-full text-xs rounded-xl border-gray-300 px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Catatan Tambahan (Opsional)') }}</label>
                        <textarea name="approval_notes" rows="3"
                                  placeholder="{{ __('Contoh: Desain disetujui, kami siapkan transfer DP siang ini.') }}"
                                  class="w-full text-xs rounded-xl border-gray-300 px-3 py-2"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl">
                            {{ __('Ya, Setujui Penawaran') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tolak Penawaran -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="showRejectModal = false">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Tolak Penawaran Harga') }}</h3>
                    <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form action="{{ route('quotations.public.reject', $quotation->public_token) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Alasan Penolakan') }} <span class="text-rose-500">*</span></label>
                        <textarea name="rejection_reason" rows="3" required
                                  placeholder="{{ __('Contoh: Harga melebihi anggaran / Waktu pengerjaan tidak mencukupi.') }}"
                                  class="w-full text-xs rounded-xl border-gray-300 px-3 py-2"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl">
                            {{ __('Kirim Penolakan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
