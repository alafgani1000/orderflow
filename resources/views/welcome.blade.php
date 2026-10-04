<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('OrderFlow — Solusi Kelola Pesanan Custom & WhatsApp') }}</title>
    <x-favicon />

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { 
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            letter-spacing: -0.011em;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col justify-between">
    
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-brand-mark class="w-10 h-10 shrink-0" />
                <span class="font-black text-xl tracking-tight text-gray-900">Order<span class="text-indigo-600">Flow</span></span>
            </div>

            <nav class="flex items-center space-x-3">
                <x-locale-switcher />
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                        {{ __('Buka Dashboard') }} →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900">
                        {{ __('Masuk (Login)') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                        {{ __('Daftar Gratis') }}
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 flex flex-col lg:flex-row items-center justify-between gap-12">
        <div class="max-w-2xl">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-6">
                ✨ {{ __('Aplikasi Sederhana untuk Usaha Custom & Konveksi') }}
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight leading-tight mb-6">
                {{ __('Jangan Sampai Pesanan, Deadline, & Pembayaran') }} <span class="text-indigo-600">{{ __('Terlewat') }}</span> {{ __('di WhatsApp.') }}
            </h1>
            <p class="text-base sm:text-lg text-gray-600 mb-8 leading-relaxed">
                {{ __('OrderFlow membantu pemilik usaha sablon, percetakan, konveksi, dan merchandise mencatat pesanan WhatsApp dalam satu tempat. Ketahui apa yang harus dikerjakan hari ini, pesanan yang terlambat, dan siapa yang belum lunas dalam hitungan detik.') }}
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-center font-bold text-base rounded-2xl shadow-lg shadow-indigo-200 transition">
                    {{ __('Mulai Kelola Pesanan Sekarang') }}
                </a>
                <a href="{{ route('login') }}" class="px-8 py-4 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 text-center font-semibold text-base rounded-2xl transition">
                    {{ __('Sudah Punya Akun') }}
                </a>
            </div>

            <!-- Features Bullet -->
            <div class="grid grid-cols-2 gap-4 mt-10 pt-8 border-t border-gray-200 text-xs sm:text-sm text-gray-700 font-semibold">
                <div class="flex items-center">
                    <span class="text-emerald-600 me-2 font-bold">✓</span> {{ __('Nomor Pesanan Otomatis') }}
                </div>
                <div class="flex items-center">
                    <span class="text-emerald-600 me-2 font-bold">✓</span> {{ __('Update WhatsApp 1-Klik') }}
                </div>
                <div class="flex items-center">
                    <span class="text-emerald-600 me-2 font-bold">✓</span> {{ __('Pantau DP & Sisa Pelunasan') }}
                </div>
                <div class="flex items-center">
                    <span class="text-emerald-600 me-2 font-bold">✓</span> {{ __('Lampirkan File Desain/Layout') }}
                </div>
            </div>
        </div>

        <!-- Preview Mockup Card -->
        <div class="w-full lg:max-w-md bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <span class="text-xs text-gray-400 font-bold">{{ __('CONTOH TAMPILAN PESANAN') }}</span>
                    <h3 class="text-lg font-black text-gray-900">#ORD-0001</h3>
                </div>
                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full border border-indigo-200">
                    {{ __('Produksi') }}
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-gray-400 font-semibold uppercase">{{ __('Pelanggan') }}</span>
                    <p class="font-bold text-gray-900">Budi (0812-3456-7890)</p>
                </div>
                <div>
                    <span class="text-xs text-gray-400 font-semibold uppercase">{{ __('Pesanan') }}</span>
                    <p class="font-medium text-gray-800">{{ __('50 Pcs Kaos Sablon DTF Komunitas') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                    <div>
                        <span class="text-xs text-gray-400 font-semibold uppercase">{{ __('Deadline') }}</span>
                        <p class="font-bold text-amber-600">{{ __('Hari ini (Target Sore)') }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-semibold uppercase">{{ __('Sisa Tagihan') }}</span>
                        <p class="font-bold text-red-600">Rp750.000 (DP Rp500k)</p>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <div class="w-full py-2.5 bg-emerald-600 text-white text-center font-bold text-xs rounded-xl shadow-xs flex items-center justify-center">
                    <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    [{{ __('Kirim Status via WhatsApp') }}]
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4">
            <strong>OrderFlow</strong> &copy; {{ date('Y') }} — {{ __('Solusi Sederhana Kelola Pesanan Custom & WhatsApp') }}
        </div>
    </footer>

</body>
</html>
