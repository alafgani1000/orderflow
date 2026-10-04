<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ __('Manajemen Toko (Tenants)') }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Kelola seluruh pemilik usaha sablon, percetakan & konveksi yang terdaftar.') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5" x-data="{ modalOpen: false, selectedTenant: null }">

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-2xs">
            <form method="GET" action="{{ route('admin.tenants.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Cari nama toko, email pemilik, atau nomor WhatsApp...') }}"
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <select name="status" class="rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Semua Status Langganan') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                    <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>{{ __('Masa Trial') }}</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('Kedaluwarsa') }}</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition">
                    {{ __('Filter') }}
                </button>
            </form>
        </div>

        <!-- Tenants Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($tenants->isEmpty())
                <x-empty-state :title="__('Tidak ada toko ditemukan')" :description="__('Coba sesuaikan kata kunci pencarian atau filter status.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3">{{ __('Nama Toko & Pemilik') }}</th>
                                <th class="px-5 py-3">{{ __('Paket') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3">{{ __('Masa Berlaku') }}</th>
                                <th class="px-5 py-3">{{ __('Total Pesanan') }}</th>
                                <th class="px-5 py-3">{{ __('Staf') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tenants as $t)
                                @php
                                    $sub = $t->subscription;
                                    $plan = $sub?->plan;
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-gray-900">{{ $t->business_name ?: __('Nama Toko Belum Diisi') }}</p>
                                        <p class="text-gray-400 text-[11px]">{{ $t->name }} &bull; {{ $t->email }} &bull; {{ $t->phone ?: '-' }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-gray-800">
                                        {{ $plan ? $plan->name : 'Starter' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($sub)
                                            @if($sub->isTrialing())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    {{ __('Trial (:days hari)', ['days' => $sub->days_remaining]) }}
                                                </span>
                                            @elseif($sub->isActive())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    {{ __('Aktif') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                    {{ __('Kedaluwarsa') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-[11px]">{{ __('Gratis') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        {{ $sub && $sub->getExpiryDate() ? $sub->getExpiryDate()->translatedFormat('d M Y') : __('Selamanya') }}
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-indigo-600">
                                        {{ $t->orders_count }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        {{ $t->employees_count }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <button @click="modalOpen = true; selectedTenant = {
                                            id: {{ $t->id }},
                                            name: '{{ addslashes($t->business_name ?: $t->name) }}',
                                            plan_id: '{{ $sub?->plan_id ?? 1 }}',
                                            status: '{{ $sub?->status ?? 'active' }}'
                                        }" class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-700 font-semibold text-[11px] transition">
                                            {{ __('Ubah Paket') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>

        <!-- Edit Subscription Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="modalOpen = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Ubah Paket Langganan Toko') }}</h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <p class="text-xs text-gray-500">{{ __('Toko:') }} <strong x-text="selectedTenant?.name" class="text-gray-800"></strong></p>

                <form :action="'/admin/tenants/' + selectedTenant?.id + '/subscription'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Pilih Paket') }}</label>
                        <select name="plan_id" x-model="selectedTenant.plan_id" class="w-full rounded-xl border-gray-300 text-xs focus:ring-indigo-500">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->formatted_price }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Status Langganan') }}</label>
                        <select name="status" x-model="selectedTenant.status" class="w-full rounded-xl border-gray-300 text-xs focus:ring-indigo-500">
                            <option value="active">{{ __('Aktif') }}</option>
                            <option value="trialing">{{ __('Masa Uji Coba (14 Hari)') }}</option>
                            <option value="expired">{{ __('Kedaluwarsa') }}</option>
                            <option value="cancelled">{{ __('Dibatalkan') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Perpanjang Tambahan Hari (Jika Aktif)') }}</label>
                        <input type="number" name="extend_days" value="30" min="1" max="365"
                               class="w-full rounded-xl border-gray-300 text-xs focus:ring-indigo-500">
                        <p class="text-[11px] text-gray-400 mt-1">{{ __('Default 30 hari dari tanggal sekarang / akhir periode.') }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
