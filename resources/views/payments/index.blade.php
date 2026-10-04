<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Catatan Pembayaran') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Rekap seluruh setoran DP dan pelunasan pesanan.') }}</p>
            </div>
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase text-gray-400 leading-none">{{ __('Total Terbayar') }}</p>
                    <p class="text-lg font-black text-emerald-600 leading-tight">Rp{{ number_format($totalAmount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <!-- Search -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4">
            <form method="GET" action="{{ route('payments.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Cari no. order, pelanggan, atau nama pesanan...') }}"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition">{{ __('Cari') }}</button>
                @if(request('search'))
                    <a href="{{ route('payments.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">{{ __('Reset') }}</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            @if($payments->isEmpty())
                <x-empty-state
                    :title="request('search') ? __('Tidak ada hasil ditemukan') : __('Belum ada transaksi')"
                    :description="request('search') ? __('Coba kata kunci berbeda.') : __('Setiap DP atau pelunasan yang dicatat di halaman pesanan akan muncul di sini.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th class="py-3 px-5">{{ __('Tanggal') }}</th>
                                <th class="py-3 px-4">{{ __('No. Order') }}</th>
                                <th class="py-3 px-4">{{ __('Pelanggan') }}</th>
                                <th class="py-3 px-4 hidden lg:table-cell">{{ __('Pesanan') }}</th>
                                <th class="py-3 px-4">{{ __('Metode') }}</th>
                                <th class="py-3 px-4">{{ __('Jumlah') }}</th>
                                <th class="py-3 px-5 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 text-sm">{{ $payment->payment_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $payment->payment_date->format('H:i') !== '00:00' ? $payment->payment_date->format('H:i') : '' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $payment->order) }}" class="font-bold text-indigo-600 hover:underline">
                                            #{{ $payment->order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900 text-sm">{{ $payment->order->customer->name }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 hidden lg:table-cell">
                                        <div class="text-sm text-gray-600 line-clamp-1 max-w-[160px]">{{ $payment->order->name }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 bg-gray-100 rounded-lg text-xs font-semibold text-gray-700">
                                            {{ __($payment->method_label) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-bold text-emerald-600 text-sm">
                                            Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                        </span>
                                        @if($payment->notes)
                                            <div class="text-[11px] text-gray-400 mt-0.5 truncate max-w-[120px]">{{ $payment->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <a href="{{ route('orders.show', $payment->order) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 text-xs font-semibold rounded-lg transition">
                                            {{ __('Buka Order') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
