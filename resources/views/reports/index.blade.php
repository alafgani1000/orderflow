<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Laporan Keuangan & Performa') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Analisis omset, penerimaan kas, piutang, dan metode pembayaran usaha Anda.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('Unduh Excel (.csv)') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('reports.index') }}" x-data="{ custom: '{{ request('period') === 'custom' ? '1' : '0' }}' }" class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 mr-1">{{ __('Periode:') }}</span>
                    <a href="{{ route('reports.index', ['period' => 'this_month']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ (!request('period') || request('period') === 'this_month') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ __('Bulan Ini') }}
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'last_month']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('period') === 'last_month' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ __('Bulan Lalu') }}
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'this_year']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('period') === 'this_year' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ __('Tahun Ini') }}
                    </a>
                    <button type="button" @click="custom = (custom === '1' ? '0' : '1')"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1.5 {{ request('period') === 'custom' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ __('Rentang Tanggal') }}</span>
                    </button>
                </div>

                <!-- Custom Date Inputs -->
                <div x-show="custom === '1'" x-cloak class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <input type="hidden" name="period" value="custom">
                    <div class="flex items-center gap-1.5">
                        <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                               class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-xs text-gray-400">{{ __('s/d') }}</span>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                               class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                        {{ __('Terapkan') }}
                    </button>
                </div>

                <!-- Active Period Badge -->
                <div class="text-xs font-medium text-gray-500">
                    {{ __('Menampilkan:') }} <span class="font-bold text-gray-800">{{ $periodLabel }}</span>
                </div>
            </form>
        </div>

        <!-- 4 Financial Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Omset -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Omset') }}</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">Rp {{ number_format($totalOmset, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 border-t border-gray-100 pt-2.5">
                    {{ __('Dari total :count pesanan masuk', ['count' => $totalOrdersCount]) }}
                </p>
            </div>

            <!-- Kas Masuk Diterima -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">{{ __('Kas Masuk (Real)') }}</p>
                        <p class="text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalCashReceived, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 border-t border-gray-100 pt-2.5">
                    {{ __('Pembayaran lunas & DP diterima di periode ini') }}
                </p>
            </div>

            <!-- Piutang Belum Lunas -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">{{ __('Sisa Piutang') }}</p>
                        <p class="text-2xl font-black {{ $totalUnpaidReceivables > 0 ? 'text-amber-600' : 'text-gray-400' }} mt-1">
                            Rp {{ number_format($totalUnpaidReceivables, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 border-t border-gray-100 pt-2.5">
                    {{ __('Tagihan belum dilunasi pelanggan') }}
                </p>
            </div>

            <!-- Pesanan Selesai -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Order Diselesaikan') }}</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">{{ $completedCount }} <span class="text-sm font-normal text-gray-400">/ {{ $totalOrdersCount }}</span></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 border-t border-gray-100 pt-2.5">
                    {{ __('Tingkat penyelesaian:') }} <strong>{{ $totalOrdersCount > 0 ? round(($completedCount / $totalOrdersCount) * 100) : 0 }}%</strong>
                </p>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('Distribusi Metode Pembayaran (Kas Masuk)') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">💵 {{ __('Tunai (Cash)') }}</span>
                    <p class="text-base font-bold text-gray-900 mt-1">Rp {{ number_format($paymentMethods['cash'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">🏦 {{ __('Transfer Bank') }}</span>
                    <p class="text-base font-bold text-gray-900 mt-1">Rp {{ number_format($paymentMethods['transfer'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">📱 QRIS / E-Wallet</span>
                    <p class="text-base font-bold text-gray-900 mt-1">Rp {{ number_format($paymentMethods['qris'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">💳 {{ __('Lainnya') }}</span>
                    <p class="text-base font-bold text-gray-900 mt-1">Rp {{ number_format($paymentMethods['other'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Orders Table for the Period -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Rincian Pesanan Masuk (:count)', ['count' => $orders->total()]) }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Daftar transaksi dan status pembayaran pada periode ini.') }}</p>
                </div>
            </div>

            @if($orders->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">{{ __('Tidak ada data pesanan') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Tidak ada pesanan yang tercatat pada rentang waktu ini.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th class="py-3 px-5">{{ __('No. Order') }}</th>
                                <th class="py-3 px-4">{{ __('Customer') }}</th>
                                <th class="py-3 px-4">{{ __('Pesanan & Ukuran') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Total Tagihan') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Terbayar') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Sisa') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                                <th class="py-3 px-5 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="font-bold text-indigo-600 hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->created_at->translatedFormat('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $order->customer->name }}</div>
                                        @if($order->customer->phone)
                                            <div class="text-[11px] text-gray-400">{{ $order->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-gray-800">{{ $order->name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $order->quantity }} pcs
                                            @if($order->has_size_breakdown)
                                                <span class="text-indigo-600 font-medium">({{ $order->size_summary }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap font-bold text-gray-900">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap font-semibold text-emerald-600">
                                        Rp {{ number_format($order->total_paid, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap font-semibold {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                        Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <x-status-badge :status="$order->status" />
                                    </td>
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition">
                                            {{ __('Detail') }}
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
