<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                        SUPER ADMIN
                    </span>
                    <h1 class="text-xl font-bold text-gray-900">{{ __('Platform SaaS Control Center') }}</h1>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Monitoring pertumbuhan toko, omset langganan (MRR), dan verifikasi pembayaran.') }}</p>
            </div>

            <!-- Admin Nav Shortcuts -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.tenants.index') }}" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl shadow-2xs transition">
                    {{ __('Kelola Semua Toko') }} ({{ $totalTenants }})
                </a>
                <a href="{{ route('admin.payments.index') }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    {{ __('Verifikasi Pembayaran') }}
                    @if($pendingPaymentsCount > 0)
                        <span class="w-4 h-4 rounded-full bg-rose-500 text-white text-[10px] flex items-center justify-center font-bold">
                            {{ $pendingPaymentsCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- 6 KPI Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Toko') }}</p>
                <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalTenants }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Penyewa terdaftar') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-500">{{ __('Toko Aktif') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $activeSubscriptions }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Berlangganan lunas') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-500">{{ __('Masa Trial') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $trialSubscriptions }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Uji coba 14 hari') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500">{{ __('Expired / Tunggakan') }}</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $expiredSubscriptions }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Perlu perpanjangan') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-500">{{ __('Estimasi MRR') }}</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">Rp{{ number_format($estimatedMRR, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Pendapatan bulanan') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-purple-500">{{ __('Total Pesanan') }}</p>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ number_format($totalPlatformOrders, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Seluruh platform') }}</p>
            </div>
        </div>

        <!-- Pending Payments Approval Queue -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Menunggu Verifikasi Pembayaran Langganan') }}</h2>
                    @if($pendingPaymentsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            {{ __(':count tertunda', ['count' => $pendingPaymentsCount]) }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('admin.payments.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    {{ __('Lihat Semua Tagihan') }} &rarr;
                </a>
            </div>

            @if($pendingInvoices->isEmpty())
                <div class="py-8 text-center text-xs text-gray-400">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Semua pembayaran langganan telah diverifikasi. Tidak ada antrean tertunda.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3">{{ __('Invoice') }}</th>
                                <th class="px-5 py-3">{{ __('Nama Toko & Pemilik') }}</th>
                                <th class="px-5 py-3">{{ __('Paket Diminta') }}</th>
                                <th class="px-5 py-3">{{ __('Nominal') }}</th>
                                <th class="px-5 py-3">{{ __('Bukti Transfer') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pendingInvoices as $inv)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-gray-900">{{ $inv->user->business_name ?: __('Nama Toko Belum Diisi') }}</p>
                                        <p class="text-gray-400 text-[11px]">{{ $inv->user->name }} ({{ $inv->user->email }})</p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="font-bold text-gray-800">{{ $inv->plan?->name }}</span>
                                        <p class="text-[10px] text-gray-400 capitalize">{{ $inv->payment_method }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-emerald-600">
                                        {{ $inv->formatted_amount }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($inv->payment_proof)
                                            <a href="{{ route('admin.payments.proof', $inv) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                {{ __('Lihat Bukti') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic">{{ __('Tanpa Berkas') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right space-x-2">
                                        <form action="{{ route('admin.payments.approve', $inv) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs transition">
                                                {{ __('Setujui & Aktifkan') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Registered Shops -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">{{ __('Toko / Tenant Terbaru Terdaftar') }}</h2>
                <a href="{{ route('admin.tenants.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    {{ __('Lihat Semua') }} ({{ $totalTenants }}) &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">{{ __('Nama Toko') }}</th>
                            <th class="px-5 py-3">{{ __('Pemilik & Kontak') }}</th>
                            <th class="px-5 py-3">{{ __('Paket Langganan') }}</th>
                            <th class="px-5 py-3">{{ __('Total Pesanan') }}</th>
                            <th class="px-5 py-3">{{ __('Karyawan') }}</th>
                            <th class="px-5 py-3">{{ __('Tgl Daftar') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Masa Aktif') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentTenants as $t)
                            @php
                                $sub = $t->subscription;
                                $plan = $sub?->plan;
                            @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-gray-900">{{ $t->business_name ?: __('Belum Ada Nama Toko') }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-gray-800">{{ $t->name }}</p>
                                    <p class="text-gray-400 text-[11px]">{{ $t->email }} &bull; {{ $t->phone ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-gray-900">{{ $plan ? $plan->name : 'Starter' }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-indigo-600">
                                    {{ __(':count pesanan', ['count' => $t->orders_count]) }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    {{ __(':count staf', ['count' => $t->employees_count]) }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-500">
                                    {{ $t->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
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
                                        <span class="text-gray-400 text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
