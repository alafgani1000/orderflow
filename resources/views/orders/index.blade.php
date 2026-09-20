<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Pesanan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola alur pengerjaan seluruh pesanan usaha Anda.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- View Mode Toggle -->
                <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 shadow-xs">
                    <a href="{{ route('orders.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Tabel
                    </a>
                    <a href="{{ route('orders.kanban') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </a>
                </div>

                @if(!auth()->user()->isProduction())
                    <a href="{{ route('orders.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Pesanan Baru
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <!-- Quick Status Filter Pills -->
        @php
            $currentStatus = request('status');
            $currentDeadline = request('deadline');
            $currentPayment = request('payment');
            $isAll = empty($currentStatus) && empty($currentDeadline) && empty($currentPayment);
        @endphp
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $isAll ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span>Semua</span>
            </a>

            <a href="{{ route('orders.index', ['status' => 'new']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentStatus === 'new' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span class="w-2 h-2 rounded-full {{ $currentStatus === 'new' ? 'bg-white' : 'bg-blue-500' }}"></span>
                <span>Baru</span>
            </a>

            <a href="{{ route('orders.index', ['status' => 'waiting_design']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentStatus === 'waiting_design' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span class="w-2 h-2 rounded-full {{ $currentStatus === 'waiting_design' ? 'bg-white' : 'bg-purple-500' }}"></span>
                <span>Menunggu Desain</span>
            </a>

            <a href="{{ route('orders.index', ['status' => 'production']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentStatus === 'production' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span class="w-2 h-2 rounded-full {{ $currentStatus === 'production' ? 'bg-white' : 'bg-amber-500' }}"></span>
                <span>Sedang Produksi</span>
            </a>

            <a href="{{ route('orders.index', ['status' => 'completed']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentStatus === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span class="w-2 h-2 rounded-full {{ $currentStatus === 'completed' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>Selesai</span>
            </a>

            @if(auth()->user()->canViewFinances())
                <a href="{{ route('orders.index', ['payment' => 'unpaid']) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentPayment === 'unpaid' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                    <span class="w-2 h-2 rounded-full {{ $currentPayment === 'unpaid' ? 'bg-white' : 'bg-rose-500' }}"></span>
                    <span>Belum Lunas</span>
                </a>
            @endif

            <a href="{{ route('orders.index', ['deadline' => 'today']) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $currentDeadline === 'today' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                <span class="w-2 h-2 rounded-full {{ $currentDeadline === 'today' ? 'bg-white' : 'bg-red-500' }}"></span>
                <span>Deadline Hari Ini</span>
            </a>
        </div>

        <!-- Filter Row -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4">
            <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @if(request('payment'))
                    <input type="hidden" name="payment" value="{{ request('payment') }}">
                @endif
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari no. order, nama pesanan..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <select name="status" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="deadline" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                    <option value="">Semua Deadline</option>
                    <option value="today" {{ request('deadline') === 'today' ? 'selected' : '' }}>Jatuh Tempo Hari Ini</option>
                    <option value="overdue" {{ request('deadline') === 'overdue' ? 'selected' : '' }}>Terlambat (Overdue)</option>
                    <option value="week" {{ request('deadline') === 'week' ? 'selected' : '' }}>7 Hari Ke Depan</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search','status','deadline','payment']))
                        <a href="{{ route('orders.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            @if($orders->isEmpty())
                <x-empty-state
                    title="Tidak ada pesanan ditemukan"
                    :description="request()->hasAny(['search','status','deadline']) ? 'Coba ubah kata kunci atau bersihkan filter.' : 'Belum ada pesanan. Buat pesanan pertama sekarang!'"
                    :actionText="request()->hasAny(['search','status','deadline']) ? null : 'Buat Pesanan Baru'"
                    :actionUrl="request()->hasAny(['search','status','deadline']) ? null : route('orders.create')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th class="py-3 px-5">No. Order</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Pesanan</th>
                                <th class="py-3 px-4">Deadline</th>
                                <th class="py-3 px-4">Status</th>
                                @if(auth()->user()->canViewFinances())
                                    <th class="py-3 px-4">Total & Sisa</th>
                                @else
                                    <th class="py-3 px-4">Rincian Ukuran</th>
                                @endif
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="font-bold text-indigo-600 hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <a href="{{ route('customers.show', $order->customer) }}" class="font-semibold text-gray-900 hover:text-indigo-600 text-sm">
                                            {{ $order->customer->name }}
                                        </a>
                                        @if($order->customer->phone)
                                            <div class="text-[11px] text-gray-400">{{ $order->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-gray-800 text-sm line-clamp-1">{{ $order->name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $order->quantity }} pcs</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($order->deadline)
                                            @if($order->is_overdue)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                    ⚠ Lewat {{ $order->deadline->format('d M') }}
                                                </span>
                                            @elseif($order->is_due_today)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⏰ Hari ini
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-700">{{ $order->deadline->format('d M Y') }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <x-status-badge :status="$order->status"/>
                                    </td>
                                    @if(auth()->user()->canViewFinances())
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-gray-900 text-sm">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                            <div class="text-[11px] mt-0.5 {{ $order->remaining_amount > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600 font-semibold' }}">
                                                {{ $order->remaining_amount > 0 ? 'Sisa Rp' . number_format($order->remaining_amount, 0, ',', '.') : '✓ Lunas' }}
                                            </div>
                                        </td>
                                    @else
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="text-xs text-gray-600 font-medium">
                                                {{ $order->has_size_breakdown ? $order->size_summary : ($order->quantity . ' pcs') }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 text-xs font-semibold rounded-lg transition">
                                            Detail →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
