<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Langganan & Kuota Toko</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola paket langganan, pantau penggunaan kuota bulanan, dan riwayat tagihan.</p>
            </div>
            @if($subscription && $subscription->isTrialing())
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Masa Uji Coba: Sisa {{ $subscription->days_remaining }} Hari
                </div>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- 1. Current Plan & Quota Meters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Current Plan Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Paket Saat Ini</span>
                        @if($subscription)
                            @if($subscription->isTrialing())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    TRIAL 14 HARI
                                </span>
                            @elseif($subscription->isActive())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    AKTIF
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    EXPIRED
                                </span>
                            @endif
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-800">
                                BELUM ADA
                            </span>
                        @endif
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mt-2">
                        {{ $currentPlan ? $currentPlan->name : 'Paket Standar' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $currentPlan ? $currentPlan->formatted_price . ' / ' . ($currentPlan->billing_period === 'yearly' ? 'tahun' : 'bulan') : 'Gratis' }}
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100 text-xs text-gray-600">
                    @if($subscription && $subscription->getExpiryDate())
                        <div class="flex items-center justify-between">
                            <span>Berakhir Pada:</span>
                            <strong class="text-gray-900">{{ $subscription->getExpiryDate()->format('d M Y') }}</strong>
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <span>Masa Aktif:</span>
                            <strong class="text-emerald-600">Selamanya Aktif</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Quota Meter -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kuota Pesanan Bulan Ini</span>
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ $ordersCount }}</span>
                        <span class="text-xs font-semibold text-gray-400">
                            / {{ $currentPlan && $currentPlan->max_orders_per_month ? $currentPlan->max_orders_per_month . ' pesanan' : 'Unlimited (Tanpa Batas)' }}
                        </span>
                    </div>

                    <!-- Progress bar -->
                    @php
                        $orderLimit = $currentPlan?->max_orders_per_month;
                        $orderPct = $orderLimit ? min(100, round(($ordersCount / $orderLimit) * 100)) : 10;
                    @endphp
                    <div class="w-full bg-gray-100 rounded-full h-2 mt-4 overflow-hidden">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ $orderPct }}%"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500">
                    Reset otomatis setiap awal bulan kalender.
                </div>
            </div>

            <!-- Staff Account Meter -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Akun Karyawan / Staf</span>
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ $staffCount }}</span>
                        <span class="text-xs font-semibold text-gray-400">
                            / {{ $currentPlan && $currentPlan->max_employees ? $currentPlan->max_employees . ' staf' : 'Unlimited' }}
                        </span>
                    </div>

                    @php
                        $staffLimit = $currentPlan?->max_employees;
                        $staffPct = $staffLimit ? min(100, round(($staffCount / $staffLimit) * 100)) : 15;
                    @endphp
                    <div class="w-full bg-gray-100 rounded-full h-2 mt-4 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $staffPct }}%"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500 flex items-center justify-between">
                    <span>Kelola di Pengaturan Toko</span>
                    <a href="{{ route('settings.index') }}" class="text-indigo-600 font-bold hover:underline">Kelola &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 2. Available Plans Upgrade Grid -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs">
            <div class="mb-6">
                <h2 class="text-base font-bold text-gray-900">Pilihan Paket Langganan OrderFlow</h2>
                <p class="text-xs text-gray-500 mt-0.5">Tingkatkan paket untuk mendapatkan kuota tak terbatas dan fitur pelaporan finansial.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($plans as $p)
                    @php
                        $isCurrent = $currentPlan && $currentPlan->id === $p->id;
                    @endphp
                    <div class="rounded-2xl border {{ $isCurrent ? 'border-indigo-600 ring-2 ring-indigo-600/20 bg-indigo-50/20' : 'border-gray-200' }} p-5 flex flex-col justify-between hover:border-indigo-300 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">{{ $p->name }}</h3>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white">
                                        PAKET AKTIF
                                    </span>
                                @elseif($p->is_popular)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        POPULER
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ $p->description }}</p>

                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-3xl font-black text-gray-900">{{ $p->formatted_price }}</span>
                                <span class="text-xs text-gray-400 font-bold">/ {{ $p->billing_period === 'yearly' ? 'tahun' : 'bulan' }}</span>
                            </div>

                            <!-- Limits summary -->
                            <div class="mt-5 space-y-2 text-xs border-t border-gray-100 pt-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Pesanan: <strong>{{ $p->hasUnlimitedOrders() ? 'Tanpa Batas (Unlimited)' : $p->max_orders_per_month . ' / bulan' }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Akun Staf: <strong>{{ $p->hasUnlimitedEmployees() ? 'Tanpa Batas (Unlimited)' : $p->max_employees . ' staf' }}</strong></span>
                                </div>
                                @if(is_array($p->features))
                                    @foreach($p->features as $feature)
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-4">
                            @if($isCurrent && $subscription && $subscription->isActive() && !$subscription->isTrialing())
                                <a href="{{ route('billing.checkout', $p) }}" class="block w-full text-center py-2.5 rounded-xl border border-indigo-600 text-indigo-600 font-bold text-xs hover:bg-indigo-50 transition">
                                    Perpanjang Paket Ini
                                </a>
                            @else
                                <a href="{{ route('billing.checkout', $p) }}" class="block w-full text-center py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                                    {{ $p->isFree() ? 'Pilih Paket Ini' : 'Tingkatkan Sekarang' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Billing & Payment History -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Riwayat Tagihan & Pembayaran Langganan</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Daftar transaksi pembayaran paket toko Anda.</p>
                </div>
            </div>

            @if($invoices->isEmpty())
                <x-empty-state title="Belum ada riwayat tagihan" description="Transaksi pembayaran paket langganan Anda akan tercatat di sini."/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">No. Invoice</th>
                                <th class="px-5 py-3">Paket</th>
                                <th class="px-5 py-3">Nominal</th>
                                <th class="px-5 py-3">Metode Bayar</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoices as $inv)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-gray-800">
                                        {{ $inv->plan?->name }}
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-gray-900">
                                        {{ $inv->formatted_amount }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 capitalize">
                                        {{ $inv->payment_method }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500">
                                        {{ $inv->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if($inv->isPaid())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                Lunas
                                            </span>
                                        @elseif($inv->isPending())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">
                                                Menunggu Verifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                Ditolak
                                            </span>
                                        @endif
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
