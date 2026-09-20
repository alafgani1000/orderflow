<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Papan Workshop (Kanban)</h1>
                <p class="text-xs text-gray-500 mt-0.5">Pantau dan geser kartu pesanan secara visual antar tahap produksi.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- View Mode Toggle -->
                <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 shadow-xs">
                    <a href="{{ route('orders.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Tabel
                    </a>
                    <a href="{{ route('orders.kanban') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </a>
                </div>

                @if(!auth()->user()->isProduction())
                    <a href="{{ route('orders.create') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Pesanan Baru
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Search bar -->
    <div class="mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-3 max-w-md">
            <form method="GET" action="{{ route('orders.kanban') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Filter no order, pelanggan..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-xl transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('orders.kanban') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Kanban Board Container (Horizontal Scrollable) -->
    <div class="overflow-x-auto pb-6 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex items-start gap-4 min-w-[1280px]">
            @foreach($columns as $statusKey => $column)
                <div class="w-72 shrink-0 bg-gray-100/80 rounded-2xl border border-gray-200/80 p-3 flex flex-col max-h-[calc(100vh-210px)]"
                     ondragover="onDragOver(event)"
                     ondragleave="onDragLeave(event)"
                     ondrop="onDrop(event, '{{ $statusKey }}')"
                     data-status="{{ $statusKey }}">
                    
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-3 px-1 border-b border-gray-200/70 mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-gray-800 tracking-tight">{{ $column['label'] }}</span>
                        </div>
                        <span class="w-5 h-5 rounded-full bg-white border border-gray-200 text-gray-700 text-[10px] font-bold flex items-center justify-center column-count">
                            {{ $column['orders']->count() }}
                        </span>
                    </div>

                    <!-- Column Cards Container -->
                    <div class="space-y-2.5 overflow-y-auto pr-1 flex-1 min-h-[140px] drop-zone" data-status="{{ $statusKey }}">
                        @forelse($column['orders'] as $order)
                            <div class="order-card bg-white rounded-xl border border-gray-200 shadow-xs p-3.5 space-y-2.5 hover:shadow-md hover:border-indigo-300 transition cursor-grab active:cursor-grabbing"
                                 draggable="true"
                                 ondragstart="onDragStart(event, '{{ $order->id }}', '{{ $order->order_number }}')"
                                 id="order-card-{{ $order->id }}"
                                 data-order-id="{{ $order->id }}">
                                
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-black text-xs text-indigo-600">#{{ $order->order_number }}</span>
                                    @if($order->deadline)
                                        @if($order->is_overdue)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                Lewat {{ $order->deadline->format('d M') }}
                                            </span>
                                        @elseif($order->is_due_today)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Hari ini
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-500 font-medium">
                                                {{ $order->deadline->format('d M') }}
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                <div>
                                    <h4 class="font-bold text-xs text-gray-900 line-clamp-1">{{ $order->name }}</h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">{{ $order->customer->name }}</p>
                                </div>

                                <!-- Quantity & Size Breakdown Summary -->
                                <div class="pt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 font-semibold text-[10px]">
                                        {{ $order->quantity }} pcs
                                    </span>

                                    @if($order->has_size_breakdown)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold text-[10px]" title="{{ $order->size_summary }}">
                                            👕 {{ Str::limit($order->size_summary, 16) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Financial info (only for owner/admin_cs) -->
                                @if(auth()->user()->canViewFinances())
                                    <div class="pt-1.5 border-t border-gray-100 flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-gray-800">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                        <span class="{{ $order->remaining_amount > 0 ? 'text-amber-600 font-bold' : 'text-emerald-600 font-bold' }}">
                                            {{ $order->remaining_amount > 0 ? 'Sisa ' . number_format($order->remaining_amount, 0, ',', '.') : '✓ Lunas' }}
                                        </span>
                                    </div>
                                @endif

                                <!-- Card Actions -->
                                <div class="pt-1 flex items-center justify-between gap-1">
                                    <a href="{{ route('orders.show', $order) }}" class="text-[11px] font-semibold text-indigo-600 hover:underline">
                                        Detail →
                                    </a>

                                    @if($order->customer->phone && !auth()->user()->isProduction())
                                        <a href="https://wa.me/{{ $order->customer->whats_app_number }}" target="_blank"
                                           class="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 transition" title="Chat WA">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="empty-placeholder py-8 text-center text-[11px] text-gray-400">
                                Tidak ada pesanan
                            </div>
                        @endforelse
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    <!-- Floating Toast Notification -->
    <div id="kanban-toast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-gray-900 text-white px-4 py-3 rounded-2xl shadow-xl border border-gray-800 text-xs font-semibold flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="kanban-toast-msg">Status pesanan diperbarui</span>
        </div>
    </div>

    <script>
        let draggedOrderId = null;
        let draggedOrderNumber = '';

        function onDragStart(e, orderId, orderNumber) {
            draggedOrderId = orderId;
            draggedOrderNumber = orderNumber;
            e.dataTransfer.setData('text/plain', orderId);
            e.dataTransfer.effectAllowed = 'move';
            document.getElementById('order-card-' + orderId).classList.add('opacity-40');
        }

        function onDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const col = e.currentTarget;
            col.classList.add('bg-indigo-50/60', 'border-indigo-300');
        }

        function onDragLeave(e) {
            const col = e.currentTarget;
            col.classList.remove('bg-indigo-50/60', 'border-indigo-300');
        }

        async function onDrop(e, targetStatus) {
            e.preventDefault();
            const col = e.currentTarget;
            col.classList.remove('bg-indigo-50/60', 'border-indigo-300');

            if (!draggedOrderId) return;

            const card = document.getElementById('order-card-' + draggedOrderId);
            if (card) {
                card.classList.remove('opacity-40');
            }

            const targetZone = col.querySelector('.drop-zone');
            if (card && targetZone) {
                // Pindahkan kartu secara visual
                const emptyPlaceholder = targetZone.querySelector('.empty-placeholder');
                if (emptyPlaceholder) {
                    emptyPlaceholder.remove();
                }
                targetZone.prepend(card);
            }

            // Kirim request update ke server
            try {
                const response = await fetch(`/orders/${draggedOrderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: targetStatus })
                });

                const data = await response.json();
                if (response.ok) {
                    showToast(`Pesanan #${draggedOrderNumber} diubah ke: ${data.status_label}`);
                    updateColumnCounts();
                } else {
                    alert('Gagal mengubah status: ' + (data.message || 'Terjadi kesalahan'));
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menghubungkan ke server.');
                window.location.reload();
            }

            draggedOrderId = null;
        }

        function updateColumnCounts() {
            document.querySelectorAll('[data-status]').forEach(col => {
                const countBadge = col.querySelector('.column-count');
                const cards = col.querySelectorAll('.order-card');
                if (countBadge) {
                    countBadge.innerText = cards.length;
                }
            });
        }

        function showToast(message) {
            const toast = document.getElementById('kanban-toast');
            const msg = document.getElementById('kanban-toast-msg');
            msg.innerText = message;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        document.addEventListener('dragend', () => {
            if (draggedOrderId) {
                const card = document.getElementById('order-card-' + draggedOrderId);
                if (card) card.classList.remove('opacity-40');
            }
        });
    </script>
</x-app-layout>
