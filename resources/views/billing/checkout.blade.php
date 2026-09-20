<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('billing.index') }}" class="p-2 rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-gray-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pembayaran Langganan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Selesaikan pembayaran untuk mengaktifkan paket {{ $plan->name }}.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Left: Plan Summary -->
        <div class="md:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Ringkasan Pesanan</span>
                <h2 class="text-xl font-black text-gray-900 mt-3">{{ $plan->name }}</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $plan->description }}</p>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-baseline justify-between">
                    <span class="text-xs text-gray-600">Total Tagihan:</span>
                    <span class="text-xl font-black text-indigo-600">{{ $plan->formatted_price }}</span>
                </div>
                <p class="text-[10px] text-gray-400 text-right mt-0.5">Periode: 1 {{ $plan->billing_period === 'yearly' ? 'Tahun' : 'Bulan' }}</p>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>{{ $plan->hasUnlimitedOrders() ? 'Unlimited Pesanan' : $plan->max_orders_per_month . ' Pesanan/bln' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>{{ $plan->hasUnlimitedEmployees() ? 'Unlimited Akun Staf' : $plan->max_employees . ' Akun Staf' }}</span>
                    </div>
                </div>
            </div>

            <!-- Help box -->
            <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 text-xs text-indigo-900">
                <p class="font-bold flex items-center gap-1.5 mb-1">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bantuan Langganan
                </p>
                Butuh bantuan pembayaran atau faktur pajak? Hubungi WhatsApp tim billing kami di <strong>0812-3456-7890</strong>.
            </div>
        </div>

        <!-- Right: Payment Instructions & Confirmation Form -->
        <div class="md:col-span-2 space-y-6">
            <!-- Payment Instructions -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs space-y-5">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">1. Pilih Rekening Tujuan Transfer</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Silakan transfer tepat sebesar <strong class="text-gray-900">{{ $plan->formatted_price }}</strong> ke salah satu rekening resmi OrderFlow:</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-indigo-900">Bank BCA</span>
                            <span class="text-[10px] font-mono bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">BCA</span>
                        </div>
                        <p class="font-mono text-base font-black text-gray-900 mt-2 tracking-wider">8820-9182-3341</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">a.n. PT OrderFlow Teknologi</p>
                    </div>

                    <div class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-amber-900">Bank Mandiri</span>
                            <span class="text-[10px] font-mono bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">MANDIRI</span>
                        </div>
                        <p class="font-mono text-base font-black text-gray-900 mt-2 tracking-wider">131-00-9876543-2</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">a.n. PT OrderFlow Teknologi</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl border border-indigo-100 bg-indigo-50/40 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-indigo-950 font-medium">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span>Menerima juga pembayaran via <strong>QRIS Semua Bank / E-Wallet</strong></span>
                    </div>
                    <span class="text-[10px] font-bold uppercase bg-white border border-indigo-200 text-indigo-700 px-2 py-0.5 rounded">QRIS Ready</span>
                </div>
            </div>

            <!-- Upload Confirmation Form -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-2xs">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-900">2. Konfirmasi Pembayaran Anda</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Unggah bukti transfer Anda agar akun langsung diverifikasi oleh Super Admin.</p>
                </div>

                <form action="{{ route('billing.confirm') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Metode Pembayaran Yang Digunakan</label>
                        <select name="payment_method" required class="w-full rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Transfer BCA">Transfer Bank BCA</option>
                            <option value="Transfer Mandiri">Transfer Bank Mandiri</option>
                            <option value="Transfer Bank Lain">Transfer Bank Lain (BRI / BNI / BSI)</option>
                            <option value="QRIS">QRIS (GoPay, OVO, ShopeePay, Dana)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Transfer / Struk (Foto/Screenshot)</label>
                        <input type="file" name="payment_proof" accept="image/*,.pdf" required
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-xl p-1.5">
                        <p class="text-[11px] text-gray-400 mt-1">Format gambar: JPG, PNG, atau PDF (Maksimal 5MB).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Transfer dari rekening a.n. Budi"
                               class="w-full rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-200 transition">
                            Kirim Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
