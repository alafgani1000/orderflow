<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Detail kontak & riwayat seluruh pesanan.') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($customer->phone)
                    <a href="https://wa.me/{{ $customer->whats_app_number }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        {{ __('Chat WA') }}
                    </a>
                @endif
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs rounded-xl shadow-xs transition">
                    {{ __('Edit Pelanggan') }}
                </a>
                <a href="{{ route('quotations.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-xl shadow-xs transition">
                    {{ __('+ Penawaran') }}
                </a>
                <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                    {{ __('+ Order Baru') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Customer Info Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 bg-gray-50/75 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ __('Nomor WhatsApp') }}</span>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">
                        {{ $customer->phone ?: __('Belum diisi') }}
                    </p>
                </div>
                <div class="p-3 bg-gray-50/75 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ __('Alamat Pengiriman') }}</span>
                    <p class="text-sm text-gray-700 mt-0.5">
                        {{ $customer->address ?: __('Belum ada alamat') }}
                    </p>
                </div>
                <div class="p-3 bg-gray-50/75 rounded-xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ __('Catatan Khusus') }}</span>
                    <p class="text-sm text-gray-700 mt-0.5">
                        {{ $customer->notes ?: '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Orders History -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Riwayat Seluruh Pesanan') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Total :count pesanan tercatat untuk pelanggan ini.', ['count' => $orders->count()]) }}</p>
                </div>
                <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    {{ __('+ Buat Pesanan') }}
                </a>
            </div>

            @if($orders->isEmpty())
                <x-empty-state 
                    :title="__('Belum ada riwayat pesanan')"
                    :description="__('Pelanggan ini belum memiliki pesanan aktif atau selesai.')"
                    :actionText="__('Buat Pesanan Pertama')"
                    :actionUrl="route('orders.create', ['customer_id' => $customer->id])"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th scope="col" class="py-3 px-5">{{ __('No. Order') }}</th>
                                <th scope="col" class="py-3 px-4">{{ __('Nama Pesanan') }}</th>
                                <th scope="col" class="py-3 px-4">{{ __('Jumlah') }}</th>
                                <th scope="col" class="py-3 px-4">{{ __('Total & Sisa') }}</th>
                                <th scope="col" class="py-3 px-4">{{ __('Deadline') }}</th>
                                <th scope="col" class="py-3 px-4">{{ __('Status') }}</th>
                                <th scope="col" class="py-3 px-5 text-end">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-5 font-bold text-indigo-600 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">
                                        {{ $order->name }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-gray-600">
                                        {{ $order->quantity }} pcs
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900 text-sm">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                        <div class="text-[11px] {{ $order->remaining_amount > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600 font-semibold' }}">
                                            {{ $order->remaining_amount > 0 ? __('Sisa Rp') . number_format($order->remaining_amount, 0, ',', '.') : '✓ ' . __('Lunas') }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ $order->deadline ? $order->deadline->format('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <x-status-badge :status="$order->status" />
                                    </td>
                                    <td class="py-3.5 px-5 text-end whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 text-xs font-semibold rounded-lg transition">
                                            {{ __('Detail') }} →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
