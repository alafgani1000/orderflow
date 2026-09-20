<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Pelanggan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola kontak dan riwayat pesanan pelanggan Anda.</p>
            </div>
            <a href="{{ route('customers.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pelanggan
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        <!-- Search -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4">
            <form method="GET" action="{{ route('customers.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama atau nomor WhatsApp..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition">Cari</button>
                @if(request('search'))
                    <a href="{{ route('customers.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            @if($customers->isEmpty())
                <x-empty-state
                    title="{{ request('search') ? 'Pelanggan tidak ditemukan' : 'Belum ada pelanggan' }}"
                    :description="request('search') ? 'Coba ubah kata kunci pencarian.' : 'Tambahkan pelanggan pertama Anda untuk mulai mencatat pesanan.'"
                    :actionText="request('search') ? null : 'Tambah Pelanggan Baru'"
                    :actionUrl="request('search') ? null : route('customers.create')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th class="py-3 px-5">Nama</th>
                                <th class="py-3 px-4">WhatsApp</th>
                                <th class="py-3 px-4 hidden md:table-cell">Alamat</th>
                                <th class="py-3 px-4">Pesanan</th>
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customers as $customer)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <a href="{{ route('customers.show', $customer) }}" class="font-semibold text-gray-900 hover:text-indigo-600">
                                            {{ $customer->name }}
                                        </a>
                                        @if($customer->notes)
                                            <div class="text-[11px] text-gray-400 mt-0.5 truncate max-w-[180px]">{{ $customer->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($customer->phone)
                                            <a href="https://wa.me/{{ $customer->whats_app_number }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 text-emerald-700 text-xs font-medium bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg border border-emerald-200 transition">
                                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                                </svg>
                                                {{ $customer->phone }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-sm text-gray-500 max-w-[160px] truncate hidden md:table-cell">
                                        {{ $customer->address ?: '—' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $customer->orders_count }} pesanan
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('customers.show', $customer) }}"
                                               class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                                Riwayat
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}"
                                               class="px-2.5 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 text-xs font-semibold rounded-lg transition">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($customers->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $customers->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
