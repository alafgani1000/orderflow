<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lacak Pesanan #{{ $order->order_number }} — {{ $order->user->business_name ?: 'OrderFlow' }}</title>
    <x-favicon />

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col font-sans">

    <!-- Top Simple Nav -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-xs">
        <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-xs">
                    OF
                </div>
                <div>
                    <h1 class="font-bold text-sm text-gray-900 leading-tight">
                        {{ $order->user->business_name ?: 'OrderFlow Workshop' }}
                    </h1>
                    <p class="text-[11px] text-gray-400">Pelacakan Pesanan Resmi</p>
                </div>
            </div>

            @if($order->user->phone)
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $order->user->phone)) }}?text={{ urlencode('Halo kak, saya ingin tanya update pesanan #' . $order->order_number . ' (' . $order->customer->name . ')') }}" 
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-xl border border-emerald-200 transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    Chat Toko
                </a>
            @endif
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-2xl mx-auto w-full px-4 py-6 space-y-5">

        <!-- Status Card -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-5 sm:p-6 space-y-5">
            <div class="flex items-start justify-between gap-3 pb-4 border-b border-gray-100">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">No. Order</span>
                    <h2 class="text-2xl font-black text-gray-900 mt-0.5">#{{ $order->order_number }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pemesan: <span class="font-semibold text-gray-800">{{ $order->customer->name }}</span></p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$order->status" class="text-xs py-1 px-3" />
                    <p class="text-[11px] text-gray-400 mt-1.5">{{ $order->created_at->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <!-- Item Details -->
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Pekerjaan / Item</span>
                <h3 class="text-base font-bold text-gray-900 mt-0.5">{{ $order->name }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Jumlah: <span class="font-semibold text-gray-800">{{ $order->quantity }} pcs</span></p>

                @if($order->description)
                    <div class="mt-2.5 p-3 rounded-xl bg-gray-50 text-xs text-gray-600 whitespace-pre-line border border-gray-100 leading-relaxed">
                        {{ $order->description }}
                    </div>
                @endif

                @if($order->has_size_breakdown)
                    <div class="mt-2.5 p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                        <span class="text-[10px] uppercase font-bold text-indigo-900 block mb-1.5">Rincian Ukuran Disepakati:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($order->size_breakdown as $size => $qty)
                                @if($qty > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-white border border-indigo-200 text-xs font-bold text-indigo-900">
                                        <span class="text-indigo-600 font-semibold">{{ $size }}:</span>
                                        <span>{{ $qty }} pcs</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Visual Progress Tracker -->
            @php
                $pipelineSteps = [
                    'new' => ['label' => 'Pesanan Diterima', 'desc' => 'Menunggu antrean'],
                    'waiting_design' => ['label' => 'Proses Desain', 'desc' => 'Layout & setting'],
                    'design_approved' => ['label' => 'Desain Disetujui', 'desc' => 'Siap cetak'],
                    'production' => ['label' => 'Sedang Produksi', 'desc' => 'Pencetakan / sablon'],
                    'completed' => ['label' => 'Pesanan Selesai', 'desc' => 'Finishing & QC'],
                    'delivered' => ['label' => 'Sudah Diambil', 'desc' => 'Selesai diserahkan'],
                ];
                $stepKeys = array_keys($pipelineSteps);
                $currentIndex = array_search($order->status, $stepKeys);
                if ($order->status === 'cancelled') {
                    $currentIndex = -1;
                }
            @endphp

            <div class="pt-2">
                <span class="text-[11px] font-bold uppercase tracking-wide text-gray-400 block mb-3">Tahapan Pengerjaan</span>

                @if($order->status === 'cancelled')
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        Pesanan ini berstatus DIBATALKAN. Silakan hubungi kami untuk informasi lebih lanjut.
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($pipelineSteps as $pKey => $step)
                            @php
                                $sIdx = array_search($pKey, $stepKeys);
                                $isDone = $currentIndex !== false && $sIdx < $currentIndex;
                                $isCurrent = $pKey === $order->status;
                                $isPending = $currentIndex !== false && $sIdx > $currentIndex;
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-2xl border transition-all
                                        {{ $isCurrent ? 'bg-indigo-50/70 border-indigo-200 ring-2 ring-indigo-500/10' : ($isDone ? 'bg-emerald-50/30 border-emerald-100' : 'bg-gray-50/50 border-gray-100 opacity-60') }}">
                                <div class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0
                                            {{ $isCurrent ? 'bg-indigo-600 text-white shadow-xs' : ($isDone ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                                    @if($isDone)
                                        ✓
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold {{ $isCurrent ? 'text-indigo-900' : ($isDone ? 'text-emerald-900' : 'text-gray-600') }}">
                                        {{ $step['label'] }}
                                    </div>
                                    <div class="text-[10px] {{ $isCurrent ? 'text-indigo-600 font-medium' : ($isDone ? 'text-emerald-600' : 'text-gray-400') }}">
                                        {{ $isCurrent ? 'Sedang Diproses Saat Ini' : ($isDone ? 'Selesai' : $step['desc']) }}
                                    </div>
                                </div>
                                @if($isCurrent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                        Aktif
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Target Deadline -->
            @if($order->deadline)
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Target Selesai / Deadline:
                    </span>
                    <span class="font-bold text-gray-900">
                        {{ $order->deadline->translatedFormat('d F Y') }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Payment Status Card -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Ringkasan Pembayaran</span>
                @if($order->is_paid_off)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        ✓ LUNAS
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        BELUM LUNAS
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Total Biaya</span>
                    <span class="text-xs sm:text-sm font-bold text-gray-900 mt-0.5 block">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                    <span class="text-[10px] uppercase font-bold text-emerald-600 block">Sudah Dibayar</span>
                    <span class="text-xs sm:text-sm font-bold text-emerald-700 mt-0.5 block">Rp{{ number_format($order->total_paid, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 rounded-2xl {{ $order->remaining_amount > 0 ? 'bg-amber-50/50 border-amber-100' : 'bg-gray-50 border-gray-100' }}">
                    <span class="text-[10px] uppercase font-bold {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400' }} block">Sisa Bayar</span>
                    <span class="text-xs sm:text-sm font-black {{ $order->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-500' }} mt-0.5 block">
                        Rp{{ number_format($order->remaining_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Design Files (Mockup) if any -->
        @if($order->files->isNotEmpty())
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-5 sm:p-6 space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block">Lampiran File Desain &amp; Mockup</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($order->files as $file)
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span class="text-base">{{ $file->icon }}</span>
                                <span class="font-medium text-gray-800 truncate block">{{ $file->file_name }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0">{{ $file->file_size_formatted }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer Help -->
        <div class="text-center py-4 text-xs text-gray-400 space-y-1">
            <p>Halaman ini diperbarui secara live sesuai progres di workshop.</p>
            <p>&copy; {{ date('Y') }} {{ $order->user->business_name ?: 'OrderFlow' }}</p>
        </div>

    </main>
</body>
</html>
