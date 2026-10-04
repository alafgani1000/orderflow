<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ __('Verifikasi Pembayaran Langganan') }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Periksa bukti transfer dan setujui aktivasi paket toko.') }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.index') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    {{ __('Semua') }}
                </a>
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    {{ __('Menunggu Verifikasi') }}
                </a>
                <a href="{{ route('admin.payments.index', ['status' => 'paid']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'paid' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    {{ __('Lunas') }}
                </a>
                <a href="{{ route('admin.payments.index', ['status' => 'rejected']) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'rejected' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    {{ __('Ditolak') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ rejectModalOpen: false, selectedInvoice: null }">

        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($invoices->isEmpty())
                <x-empty-state :title="__('Tidak ada transaksi pembayaran')" :description="__('Belum ada tagihan langganan untuk status yang dipilih.')"/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3">{{ __('No. Invoice') }}</th>
                                <th class="px-5 py-3">{{ __('Toko & Pemilik') }}</th>
                                <th class="px-5 py-3">{{ __('Paket') }}</th>
                                <th class="px-5 py-3">{{ __('Nominal') }}</th>
                                <th class="px-5 py-3">{{ __('Metode Bayar') }}</th>
                                <th class="px-5 py-3">{{ __('Bukti Transfer') }}</th>
                                <th class="px-5 py-3">{{ __('Tanggal Tagihan') }}</th>
                                <th class="px-5 py-3">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoices as $inv)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-gray-900">{{ $inv->user->business_name ?: __('Nama Toko Belum Diisi') }}</p>
                                        <p class="text-gray-400 text-[11px]">{{ $inv->user->name }} ({{ $inv->user->email }})</p>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-gray-800">
                                        {{ $inv->plan?->name }}
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-gray-900">
                                        {{ $inv->formatted_amount }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        {{ $inv->payment_method }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($inv->payment_proof)
                                            <a href="{{ route('admin.payments.proof', $inv) }}" target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                {{ __('Lihat File') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic">{{ __('Tanpa Berkas') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-500">
                                        {{ $inv->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5">
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
                                    <td class="px-5 py-3.5 text-right space-x-1.5">
                                        @if($inv->isPending())
                                            <form action="{{ route('admin.payments.approve', $inv) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs transition">
                                                    {{ __('Setujui') }}
                                                </button>
                                            </form>
                                            <button @click="rejectModalOpen = true; selectedInvoice = { id: {{ $inv->id }}, number: '{{ $inv->invoice_number }}' }"
                                                    class="px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 font-bold text-[11px] transition">
                                                {{ __('Tolak') }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-[11px]">{{ __('Selesai') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>

        <!-- Reject Modal -->
        <div x-show="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" style="display: none;">
            <div @click.away="rejectModalOpen = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Tolak Pembayaran Langganan') }}</h3>
                    <button @click="rejectModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <p class="text-xs text-gray-500">{{ __('Invoice:') }} <strong x-text="selectedInvoice?.number" class="text-gray-800"></strong></p>

                <form :action="'/admin/payments/' + selectedInvoice?.id + '/reject'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Alasan Penolakan') }}</label>
                        <textarea name="rejection_note" required rows="3" placeholder="{{ __('Contoh: Bukti transfer tidak terbaca / dana belum masuk mutasi rekening.') }}"
                                  class="w-full rounded-xl border-gray-300 text-xs focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-800">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            {{ __('Tolak Pembayaran') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
