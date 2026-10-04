<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Penawaran Harga (Quotations)') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Kelola penawaran harga untuk calon pelanggan sebelum dikonversi menjadi pesanan produksi.') }}</p>
            </div>
            <div>
                <a href="{{ route('quotations.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Buat Penawaran Baru') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        <!-- Status Counter Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <a href="{{ route('quotations.index') }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs hover:border-indigo-300 transition {{ !request('status') ? 'ring-2 ring-indigo-500/20 border-indigo-500' : '' }}">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Semua') }}</span>
                <p class="text-lg font-black text-gray-900 mt-0.5">{{ $stats['total'] }}</p>
            </a>
            <a href="{{ route('quotations.index', ['status' => 'draft']) }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs hover:border-gray-400 transition {{ request('status') === 'draft' ? 'ring-2 ring-gray-500/20 border-gray-500' : '' }}">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Draft') }}</span>
                <p class="text-lg font-black text-gray-700 mt-0.5">{{ $stats['draft'] }}</p>
            </a>
            <a href="{{ route('quotations.index', ['status' => 'sent']) }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs hover:border-sky-300 transition {{ request('status') === 'sent' ? 'ring-2 ring-sky-500/20 border-sky-500' : '' }}">
                <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">{{ __('Terkirim') }}</span>
                <p class="text-lg font-black text-sky-700 mt-0.5">{{ $stats['sent'] }}</p>
            </a>
            <a href="{{ route('quotations.index', ['status' => 'approved']) }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs hover:border-emerald-300 transition {{ request('status') === 'approved' ? 'ring-2 ring-emerald-500/20 border-emerald-500' : '' }}">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">{{ __('Disetujui') }}</span>
                <p class="text-lg font-black text-emerald-700 mt-0.5">{{ $stats['approved'] }}</p>
            </a>
            <a href="{{ route('quotations.index', ['status' => 'converted']) }}" class="p-3 bg-white rounded-xl border border-gray-200 shadow-2xs hover:border-indigo-300 transition {{ request('status') === 'converted' ? 'ring-2 ring-indigo-500/20 border-indigo-500' : '' }}">
                <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">{{ __('Dikonversi') }}</span>
                <p class="text-lg font-black text-indigo-700 mt-0.5">{{ $stats['converted'] }}</p>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-2xs">
            <form method="GET" action="{{ route('quotations.index') }}" class="flex flex-col sm:flex-row gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Cari nomor penawaran, judul, atau nama pelanggan...') }}"
                           class="w-full text-xs rounded-xl border-gray-300 pl-9 pr-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl transition">
                        {{ __('Filter') }}
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('quotations.index') }}" class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Quotations Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($quotations->isEmpty())
                <x-empty-state :title="__('Belum ada penawaran')" :description="__('Buat penawaran harga pertama Anda dan bagikan tautan persetujuan ke pelanggan via WhatsApp.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">{{ __('No. Penawaran') }}</th>
                                <th class="px-5 py-3">{{ __('Pelanggan') }}</th>
                                <th class="px-5 py-3">{{ __('Judul Penawaran') }}</th>
                                <th class="px-5 py-3">{{ __('Total') }}</th>
                                <th class="px-5 py-3">{{ __('Berlaku Hingga') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($quotations as $q)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                        <a href="{{ route('quotations.show', $q) }}" class="hover:underline">
                                            {{ $q->quotation_number }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-gray-800">{{ $q->customer->name }}</div>
                                        @if($q->customer->phone)
                                            <div class="text-[11px] text-gray-400">{{ $q->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-medium text-gray-900 max-w-xs truncate">
                                        {{ $q->title }}
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-gray-900">
                                        Rp{{ number_format($q->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500">
                                        {{ $q->valid_until ? $q->valid_until->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $q->status_badge_class }}">
                                            {{ $q->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('quotations.show', $q) }}" title="{{ __('Lihat Rincian') }}"
                                               class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            @if(!$q->isConverted())
                                                <a href="{{ route('quotations.edit', $q) }}" title="{{ __('Edit') }}"
                                                   class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                            @endif
                                            @if($q->canBeConverted())
                                                <form action="{{ route('quotations.convert', $q) }}" method="POST" class="inline" onsubmit="return confirm(@js(__('Konversi penawaran ini menjadi pesanan produksi baru?')));">
                                                    @csrf
                                                    <button type="submit" title="{{ __('Konversi ke Pesanan') }}"
                                                            class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($quotations->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $quotations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
