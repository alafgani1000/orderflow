<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Dashboard Ringkasan') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Pantau pesanan, deadline, dan tagihan usaha Anda hari ini.') }}</p>
            </div>
            <a href="{{ route('orders.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-indigo-200/60 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Buat Pesanan Baru') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-5">
        @php
            $sub = auth()->user()->currentSubscription();
        @endphp

        @if($sub && $sub->isTrialing())
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 border border-indigo-200/80 shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">✨</span>
                    <div>
                        <p class="text-xs font-bold text-gray-900">{{ __('Masa Uji Coba Gratis Paket Pro Aktif') }}</p>
                        <p class="text-[11px] text-gray-500">{{ __('Tersisa') }} <strong>{{ $sub->days_remaining }} {{ __('hari') }}</strong> {{ __('lagi. Nikmati pesanan tanpa batas dan fitur pelaporan keuangan.') }}</p>
                    </div>
                </div>
                <a href="{{ route('billing.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    {{ __('Pilih Paket Langganan') }} &rarr;
                </a>
            </div>
        @elseif($sub && $sub->isExpired())
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-sm shrink-0">⚠️</span>
                    <div>
                        <p class="text-xs font-bold text-rose-900">{{ __('Masa Aktif Paket Anda Telah Berakhir') }}</p>
                        <p class="text-[11px] text-rose-700">{{ __('Perpanjang langganan sekarang agar Anda tetap bisa membuat pesanan baru dan mencetak invoice.') }}</p>
                    </div>
                </div>
                <a href="{{ route('billing.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    {{ __('Perpanjang Paket Sekarang') }} &rarr;
                </a>
            </div>
        @endif

        @if($onboarding && !collect($onboarding)->every(fn ($completed) => $completed))
            @php($completedSteps = collect($onboarding)->filter()->count())
            <section class="overflow-hidden rounded-2xl border border-indigo-200 bg-gradient-to-br from-white via-indigo-50/40 to-purple-50/50 shadow-xs">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-600 text-sm font-black text-white">{{ $completedSteps }}/3</span>
                            <h2 class="text-sm font-bold text-gray-900">{{ __('Checklist Aktivasi Toko') }}</h2>
                        </div>
                        <p class="text-xs text-gray-500">{{ __('Selesaikan tiga langkah ini agar OrderFlow siap dipakai untuk pesanan nyata.') }}</p>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-indigo-100 sm:mt-3 sm:w-40" role="progressbar" aria-valuenow="{{ $completedSteps }}" aria-valuemin="0" aria-valuemax="3" aria-label="{{ __('Progres aktivasi toko') }}">
                        <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ ($completedSteps / 3) * 100 }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 border-t border-indigo-100 sm:grid-cols-3 sm:divide-x sm:divide-indigo-100">
                    <a href="{{ route('onboarding.show') }}" class="group flex items-center gap-3 p-4 transition hover:bg-white/70">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black {{ $onboarding['profile'] ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-indigo-700 ring-1 ring-indigo-200' }}">
                            {{ $onboarding['profile'] ? '✓' : '1' }}
                        </span>
                        <span>
                            <span class="block text-xs font-bold text-gray-800">{{ __('Lengkapi profil toko') }}</span>
                            <span class="text-[11px] text-gray-500">{{ $onboarding['profile'] ? __('Selesai') : __('Nama, kontak, alamat, dan rekening') }}</span>
                        </span>
                    </a>
                    <a href="{{ route('customers.create') }}" class="group flex items-center gap-3 p-4 transition hover:bg-white/70">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black {{ $onboarding['customer'] ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-indigo-700 ring-1 ring-indigo-200' }}">
                            {{ $onboarding['customer'] ? '✓' : '2' }}
                        </span>
                        <span>
                            <span class="block text-xs font-bold text-gray-800">{{ __('Tambahkan pelanggan pertama') }}</span>
                            <span class="text-[11px] text-gray-500">{{ $onboarding['customer'] ? __('Selesai') : __('Siapkan data pemesan') }}</span>
                        </span>
                    </a>
                    <a href="{{ $onboarding['customer'] ? route('orders.create') : route('customers.create') }}" class="group flex items-center gap-3 p-4 transition hover:bg-white/70">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black {{ $onboarding['order'] ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-indigo-700 ring-1 ring-indigo-200' }}">
                            {{ $onboarding['order'] ? '✓' : '3' }}
                        </span>
                        <span>
                            <span class="block text-xs font-bold text-gray-800">{{ __('Buat pesanan pertama') }}</span>
                            <span class="text-[11px] text-gray-500">{{ $onboarding['order'] ? __('Selesai') : __('Mulai alur produksi') }}</span>
                        </span>
                    </a>
                </div>
            </section>
        @endif

        @if($demoData && ($demoData['exists'] || !$demoData['has_real_orders']))
            <section class="flex flex-col gap-4 rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 to-indigo-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white">✦</span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-gray-900">{{ __('Jelajahi OrderFlow dengan Data Contoh') }}</h2>
                            @if($demoData['exists'])
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700">{{ __('AKTIF') }}</span>
                            @endif
                        </div>
                        @if($demoData['exists'])
                            <p class="mt-1 text-xs text-gray-600">{{ __('Tersedia :customers pelanggan dan :orders pesanan contoh. Semuanya ditandai agar dapat dihapus tanpa menyentuh data asli.', ['customers' => $demoData['customers'], 'orders' => $demoData['orders']]) }}</p>
                        @else
                            <p class="mt-1 text-xs text-gray-600">{{ __('Buat contoh pelanggan, pesanan, deadline, dan pembayaran untuk mencoba dashboard tanpa input manual.') }}</p>
                        @endif
                    </div>
                </div>

                @if($demoData['exists'])
                    <form method="POST" action="{{ route('demo-data.destroy') }}" onsubmit="return confirm(@js(__('Hapus seluruh data contoh? Data asli toko tidak akan dihapus.')))" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-50 sm:w-auto">
                            {{ __('Hapus Data Contoh') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('demo-data.store') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-sky-700 sm:w-auto">
                            {{ __('Buat Data Contoh') }}
                        </button>
                    </form>
                @endif
            </section>
        @endif

        <!-- 4 KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card :title="__('Pesanan Aktif')" :value="$activeOrders" color="indigo"
                :subtitle="__('Dalam proses produksi')" :href="route('orders.index')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </x-stat-card>

            <x-stat-card :title="__('Jatuh Tempo Hari Ini')" :value="$dueToday" color="amber"
                :subtitle="__('Target selesai hari ini')" :href="route('orders.index', ['deadline' => 'today'])">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-stat-card>

            <x-stat-card :title="__('Terlambat')" :value="$overdue" color="rose"
                :subtitle="__('Melewati batas deadline')" :href="route('orders.index', ['deadline' => 'overdue'])">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </x-stat-card>

            @if(auth()->user()->canViewFinances())
                <x-stat-card :title="__('Belum Lunas')" :value="$unpaid" color="emerald"
                    :subtitle="__('Ada sisa tagihan/DP')" :href="route('payments.index')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-stat-card>
            @else
                <x-stat-card :title="__('Papan Workshop')" value="Kanban" color="emerald"
                    :subtitle="__('Pantau alur produksi')" :href="route('orders.kanban')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </x-stat-card>
            @endif
        </div>

        @if(auth()->user()->canViewFinances())
            <!-- Monthly Financial KPI Summary -->
            <div class="bg-gradient-to-br from-gray-900 via-gray-900 to-indigo-950 rounded-2xl p-5 text-white shadow-xs border border-gray-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-gray-800">
                    <div>
                        <div class="inline-flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <h2 class="text-sm font-bold text-white tracking-wide">{{ __('Ringkasan Keuangan Bulan Ini') }}</h2>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ now()->isoFormat('MMMM Y') }} &bull; {{ __('Perhitungan otomatis pesanan & kas bersih usaha') }}</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-300 hover:text-white transition">
                        {{ __('Laporan Detail & Export') }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Omset Bulan Ini -->
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                        <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                            <span>{{ __('Omset Pesanan Baru') }}</span>
                            <span class="p-1.5 rounded-lg bg-indigo-500/20 text-indigo-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </span>
                        </div>
                        <div class="text-xl font-black text-white tracking-tight">
                            Rp{{ number_format($monthlyRevenue, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1">{{ __('Total nilai pesanan dibuat bulan ini') }}</div>
                    </div>

                    <!-- Kas Masuk Bersih -->
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                        <div class="flex items-center justify-between text-xs text-emerald-400 mb-1">
                            <span>{{ __('Kas Masuk Bersih') }}</span>
                            <span class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="text-xl font-black text-emerald-400 tracking-tight">
                            Rp{{ number_format($monthlyCashIn, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1">{{ __('Pembayaran masuk − pengembalian refund') }}</div>
                    </div>

                    <!-- Sisa Piutang Usaha -->
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                        <div class="flex items-center justify-between text-xs text-amber-400 mb-1">
                            <span>{{ __('Total Sisa Piutang') }}</span>
                            <a href="{{ route('payments.index') }}" class="p-1.5 rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 transition" title="{{ __('Kelola Pelunasan') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </a>
                        </div>
                        <div class="text-xl font-black text-amber-400 tracking-tight">
                            Rp{{ number_format($totalPendingReceivables, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1 flex items-center justify-between">
                            <span>{{ __('Tagihan belum lunas') }}</span>
                            <a href="{{ route('payments.index') }}" class="text-amber-300 hover:underline font-semibold">{{ __('Tagih') }} &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Upcoming Deadline Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Pesanan Mendekati Deadline') }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Pesanan aktif yang perlu diprioritaskan.') }}</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline inline-flex items-center gap-1">
                    {{ __('Lihat semua') }} ({{ $activeOrders }})
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if($upcomingOrders->isEmpty())
                <x-empty-state :title="__('Tidak ada deadline mendesak')" :description="__('Semua pesanan terkendali. Buat pesanan baru untuk memulai.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                <th class="py-3 px-5">{{ __('No. Order') }}</th>
                                <th class="py-3 px-4">{{ __('Customer') }}</th>
                                <th class="py-3 px-4">{{ __('Pesanan') }}</th>
                                <th class="py-3 px-4">{{ __('Deadline') }}</th>
                                <th class="py-3 px-4">{{ __('Status') }}</th>
                                @if(auth()->user()->canViewFinances())
                                    <th class="py-3 px-4">{{ __('Sisa Bayar') }}</th>
                                @endif
                                <th class="py-3 px-5 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($upcomingOrders as $order)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="font-bold text-indigo-600 hover:underline text-sm">
                                            #{{ $order->order_number }}
                                        </a>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900 text-sm">{{ $order->customer->name }}</div>
                                        @if($order->customer->phone)
                                            <div class="text-[11px] text-gray-400">{{ $order->customer->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-sm text-gray-800 font-medium line-clamp-1">{{ $order->name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $order->quantity }} pcs</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($order->deadline)
                                            @if($order->is_overdue)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                    ⚠ {{ __('Lewat') }} {{ $order->deadline->format('d M') }}
                                                </span>
                                            @elseif($order->is_due_today)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⏰ {{ __('Hari ini') }}
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
                                            @if($order->remaining_amount > 0)
                                                <span class="text-sm font-bold text-amber-600">
                                                    Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                                    ✓ {{ __('Lunas') }}
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($order->customer->phone)
                                                <a href="{{ app(\App\Services\WhatsAppService::class)->statusUrl($order) }}"
                                                   target="_blank" title="{{ __('Kirim Status WhatsApp') }}"
                                                   class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200/60 transition">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('orders.show', $order) }}"
                                               class="px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                                {{ __('Detail') }}
                                            </a>
                                        </div>
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
