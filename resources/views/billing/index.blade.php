<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Langganan & Kuota Toko') }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Kelola paket langganan, pantau penggunaan kuota bulanan, dan riwayat tagihan.') }}</p>
            </div>
            @if($subscription && $subscription->isTrialing())
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ __('Masa Uji Coba: Sisa :days Hari', ['days' => $subscription->days_remaining]) }}
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
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Paket Saat Ini') }}</span>
                        @if($subscription)
                            @if($subscription->isTrialing())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ __('TRIAL 14 HARI') }}
                                </span>
                            @elseif($subscription->isActive())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ __('AKTIF') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    EXPIRED
                                </span>
                            @endif
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-800">
                                    {{ __('BELUM ADA') }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mt-2">
                        {{ $currentPlan ? $currentPlan->name : __('Paket Standar') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $currentPlan ? $currentPlan->formatted_price . ' / ' . ($currentPlan->billing_period === 'yearly' ? __('tahun') : __('bulan')) : __('Gratis') }}
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100 text-xs text-gray-600">
                    @if($subscription && $subscription->getExpiryDate())
                        <div class="flex items-center justify-between">
                            <span>{{ __('Berakhir Pada:') }}</span>
                            <strong class="text-gray-900">{{ $subscription->getExpiryDate()->translatedFormat('d M Y') }}</strong>
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <span>{{ __('Masa Aktif:') }}</span>
                            <strong class="text-emerald-600">{{ __('Selamanya Aktif') }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Quota Meter -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Kuota Pesanan Bulan Ini') }}</span>
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ $ordersCount }}</span>
                        <span class="text-xs font-semibold text-gray-400">
                            / {{ $currentPlan && $currentPlan->max_orders_per_month ? $currentPlan->max_orders_per_month . ' ' . __('pesanan') : __('Unlimited (Tanpa Batas)') }}
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
                    {{ __('Reset otomatis setiap awal bulan kalender.') }}
                </div>
            </div>

            <!-- Staff Account Meter -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Akun Karyawan / Staf') }}</span>
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ $staffCount }}</span>
                        <span class="text-xs font-semibold text-gray-400">
                            / {{ $currentPlan && $currentPlan->max_employees ? $currentPlan->max_employees . ' ' . __('staf') : 'Unlimited' }}
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
                    <span>{{ __('Kelola di Pengaturan Toko') }}</span>
                    <a href="{{ route('settings.index') }}" class="text-indigo-600 font-bold hover:underline">{{ __('Kelola') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 2. Available Plans Upgrade Grid -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs">
            <div class="mb-6">
                <h2 class="text-base font-bold text-gray-900">{{ __('Pilihan Paket Langganan OrderFlow') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Tingkatkan paket untuk mendapatkan kuota tak terbatas dan fitur pelaporan finansial.') }}</p>
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
                                        {{ __('PAKET AKTIF') }}
                                    </span>
                                @elseif($p->is_popular)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ __('POPULER') }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ __($p->description) }}</p>

                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-3xl font-black text-gray-900">{{ $p->formatted_price }}</span>
                                <span class="text-xs text-gray-400 font-bold">/ {{ $p->billing_period === 'yearly' ? __('tahun') : __('bulan') }}</span>
                            </div>

                            <!-- Limits summary -->
                            <div class="mt-5 space-y-2 text-xs border-t border-gray-100 pt-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>{{ __('Pesanan:') }} <strong>{{ $p->hasUnlimitedOrders() ? __('Tanpa Batas (Unlimited)') : $p->max_orders_per_month . ' / ' . __('bulan') }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>{{ __('Akun Staf:') }} <strong>{{ $p->hasUnlimitedEmployees() ? __('Tanpa Batas (Unlimited)') : $p->max_employees . ' ' . __('staf') }}</strong></span>
                                </div>
                                @if(is_array($p->features))
                                    @foreach($p->features as $feature)
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span>{{ __($feature) }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-4">
                            @if($isCurrent && $subscription && $subscription->isActive() && !$subscription->isTrialing())
                                <a href="{{ route('billing.checkout', $p) }}" class="block w-full text-center py-2.5 rounded-xl border border-indigo-600 text-indigo-600 font-bold text-xs hover:bg-indigo-50 transition">
                                    {{ __('Perpanjang Paket Ini') }}
                                </a>
                            @else
                                <a href="{{ route('billing.checkout', $p) }}" class="block w-full text-center py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                                    {{ $p->isFree() ? __('Pilih Paket Ini') : __('Tingkatkan Sekarang') }}
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
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Riwayat Tagihan & Pembayaran Langganan') }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Daftar transaksi pembayaran paket toko Anda.') }}</p>
                </div>
            </div>

            @if($invoices->isEmpty())
                <x-empty-state :title="__('Belum ada riwayat tagihan')" :description="__('Transaksi pembayaran paket langganan Anda akan tercatat di sini.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">{{ __('No. Invoice') }}</th>
                                <th class="px-5 py-3">{{ __('Paket') }}</th>
                                <th class="px-5 py-3">{{ __('Nominal') }}</th>
                                <th class="px-5 py-3">{{ __('Metode Bayar') }}</th>
                                <th class="px-5 py-3">{{ __('Tanggal') }}</th>
                                <th class="px-5 py-3">{{ __('Bukti') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Status') }}</th>
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
                                        {{ $inv->created_at->translatedFormat('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($inv->payment_proof)
                                            <a href="{{ route('billing.invoices.proof', $inv) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] transition">
                                                {{ __('Lihat Bukti') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if($inv->isPaid())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                {{ __('Lunas') }}
                                            </span>
                                        @elseif($inv->isPending())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">
                                                {{ __('Menunggu Verifikasi') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                {{ __('Ditolak') }}
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
