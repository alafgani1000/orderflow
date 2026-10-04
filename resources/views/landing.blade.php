<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('OrderFlow — Software Manajemen Pesanan Sablon, Percetakan & Konveksi') }}</title>
    <x-favicon />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-white selection:bg-indigo-500 selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <x-brand-mark class="w-10 h-10 shrink-0 transition-transform group-hover:scale-105" />
                    <span class="font-black text-2xl tracking-tight text-gray-900">Order<span class="text-indigo-600">Flow</span></span>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#fitur" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">{{ __('Fitur Utama') }}</a>
                    <a href="#solusi" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">{{ __('Target Usaha') }}</a>
                    <a href="#cara-kerja" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">{{ __('Cara Kerja') }}</a>
                    <a href="#harga" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">{{ __('Paket Harga') }}</a>
                    <a href="#faq" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">FAQ</a>
                </nav>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center gap-3">
                    <x-locale-switcher />
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                            {{ __('Masuk ke Dashboard') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-700 hover:text-indigo-600 px-4 py-2 transition">
                            {{ __('Masuk') }}
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                            {{ __('Coba Gratis 14 Hari') }}
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" class="md:hidden border-b border-gray-100 bg-white px-4 pt-2 pb-6 space-y-3">
            <a @click="mobileMenuOpen = false" href="#fitur" class="block py-2 text-sm font-semibold text-gray-700">{{ __('Fitur Utama') }}</a>
            <a @click="mobileMenuOpen = false" href="#solusi" class="block py-2 text-sm font-semibold text-gray-700">{{ __('Target Usaha') }}</a>
            <a @click="mobileMenuOpen = false" href="#cara-kerja" class="block py-2 text-sm font-semibold text-gray-700">{{ __('Cara Kerja') }}</a>
            <a @click="mobileMenuOpen = false" href="#harga" class="block py-2 text-sm font-semibold text-gray-700">{{ __('Paket Harga') }}</a>
            <a @click="mobileMenuOpen = false" href="#faq" class="block py-2 text-sm font-semibold text-gray-700">FAQ</a>
            <div class="pt-2"><x-locale-switcher /></div>
            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">{{ __('Dashboard Toko') }}</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-gray-200 text-gray-800 font-bold text-sm">{{ __('Masuk') }}</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">{{ __('Coba Gratis 14 Hari') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-12 pb-20 md:pt-20 md:pb-32 overflow-hidden bg-radial from-indigo-50/70 via-white to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-6">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    {{ __('SaaS Manajemen Usaha Custom #1 di Indonesia') }}
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 tracking-tight leading-[1.15]">
                    {{ __('Jangan Biarkan Pesanan, Deadline & Uang Muka') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">{{ __('Tercecer di WhatsApp!') }}</span>
                </h1>

                <!-- Subtitle -->
                <p class="mt-6 text-lg sm:text-xl text-gray-600 leading-relaxed font-normal">
                    {{ __('Solusi terpadu khusus pemilik') }} <strong>{{ __('sablon, percetakan, digital printing & konveksi') }}</strong> {{ __('untuk mengatur pesanan WhatsApp, papan produksi, catat DP pelunasan, hingga kirim kabar ke customer dalam 1-klik.') }}
                </p>

                <!-- CTAs -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-base font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-0.5">
                        {{ __('Mulai Uji Coba Gratis 14 Hari') }}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#cara-kerja" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl border border-gray-300 hover:border-gray-400 bg-white text-gray-700 text-base font-bold transition">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Lihat Alur Kerja') }}
                    </a>
                </div>

                <!-- Guarantee / Trust Badges -->
                <div class="mt-6 flex flex-wrap items-center justify-center gap-6 text-xs text-gray-500 font-medium">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('Tanpa Kartu Kredit') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('Setup 1 Menit Langsung Pakai') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('WhatsApp Tanpa Biaya (wa.me)') }}
                    </span>
                </div>
            </div>

            <!-- Dashboard Preview Mockup -->
            <div class="mt-14 max-w-5xl mx-auto rounded-3xl border border-gray-200/80 bg-white p-3 sm:p-4 shadow-2xl shadow-indigo-100/60">
                <div class="rounded-2xl overflow-hidden border border-gray-100 bg-gray-900">
                    <!-- Fake browser bar -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-800/80 border-b border-gray-700/50">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="px-6 py-1 bg-gray-900 rounded-lg text-xs text-gray-400 font-mono">
                            app.orderflow.id/dashboard
                        </div>
                        <div class="w-12"></div>
                    </div>

                    <!-- App UI Graphic Mockup -->
                    <div class="bg-gray-50 p-5 sm:p-8 space-y-6 text-left">
                        <!-- KPI Row -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Pesanan Aktif') }}</p>
                                <p class="text-2xl font-black text-indigo-600 mt-1">28</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('Sedang dikerjakan') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">{{ __('Jatuh Tempo Hari Ini') }}</p>
                                <p class="text-2xl font-black text-amber-600 mt-1">4</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('Prioritas selesai') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">{{ __('Terlambat (Overdue)') }}</p>
                                <p class="text-2xl font-black text-rose-600 mt-1">1</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('Perlu perhatian') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">{{ __('Omset Bulan Ini') }}</p>
                                <p class="text-2xl font-black text-emerald-600 mt-1">Rp 34,8 Jt</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('100% Tercatat') }}</p>
                            </div>
                        </div>

                        <!-- Orders sample row -->
                        <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <span class="font-bold text-sm text-gray-800">{{ __('Daftar Pengerjaan Workshop Terdekat') }}</span>
                                <span class="text-xs bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-full">{{ __('Tersinkron Langsung') }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs py-2 border-b border-gray-50">
                                <div>
                                    <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">ORD-0042</span>
                                    <span class="font-bold text-gray-800 ml-2">{{ __('50 Pcs Kaos Sablon DTF Komunitas Motor') }}</span>
                                    <p class="text-gray-400 mt-0.5">Budi Santoso &bull; WhatsApp: 081234567890</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded-lg">{{ __('Sedang Produksi') }}</span>
                                    <span class="text-emerald-600 font-bold">{{ __('DP: Rp 750.000 (Sisa Rp 500k)') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Business Types -->
    <section id="solusi" class="py-16 bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-bold uppercase tracking-widest text-indigo-600 mb-3">{{ __('Dirancang Spesifik Untuk Industri Custom') }}</p>
            <h2 class="text-center text-2xl sm:text-3xl font-black text-gray-900 mb-10">{{ __('Cocok untuk Berbagai Tipe Usaha Workshop Anda') }}</h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl mb-3">👕</div>
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Sablon & DTF') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Kaos, totebag, hoodie, jersey & merchandise.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl mb-3">🖨️</div>
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Digital Printing') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Spanduk flexi, banner, stiker & poster.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl mb-3">🧵</div>
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Konveksi Seragam') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Kemeja PDH, jaket varsity, celana & rompi.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl mb-3">💌</div>
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Percetakan & Undangan') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Undangan pernikahan, souvenir & packaging.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl mb-3">☕</div>
                    <h3 class="font-bold text-sm text-gray-900">{{ __('Souvenir & Gift') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Mug custom, tumbler grafir, gantungan kunci.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ __('Fitur Lengkap dari MVP hingga Berkembang') }}</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">{{ __('Semua yang Dibutuhkan Workshop Anda Ada di Sini') }}</h2>
                <p class="mt-3 text-base text-gray-600">{{ __('Didesain dari pengalaman nyata ribuan percetakan yang pusing melacak order dari chat WA.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Dashboard Anti Terlambat') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Pantau pesanan yang harus selesai hari ini dan yang terlambat. Jangan sampai kena komplain pelanggan karena tenggat terlewat.') }}</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('WhatsApp 1-Klik (Tanpa Biaya API)') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Kirim kabar status produksi, tanda terima pembayaran DP, dan notifikasi pesanan selesai langsung ke WhatsApp pelanggan dengan template otomatis yang rapi.') }}</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Papan Produksi Kanban') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Geser kartu pesanan dari Menunggu Desain → Disetujui → Produksi → Selesai → Diambil. Seluruh tim workshop tahu apa yang harus dikerjakan.') }}</p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Lacak Mandiri Pelanggan') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Berikan tautan publik khusus kepada pembeli agar mereka bisa melihat progres cetak dan sisa bayar tanpa terus-menerus menghubungi admin.') }}</p>
                </div>

                <!-- Feature 5 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Pencatatan DP & Invoice Resmi') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Catat uang muka (DP) dan cicilan secara terperinci. Cetak invoice yang bersih dengan logo toko Anda, siap dikirim atau dicetak sebagai surat jalan.') }}</p>
                </div>

                <!-- Feature 6 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Laporan Omset & Ekspor Excel') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Rekap pendapatan, piutang belum lunas, dan metode bayar. Ekspor ke CSV Excel aman dalam satu klik.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="cara-kerja" class="py-20 bg-gray-50 border-t border-gray-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ __('Alur Kerja Praktis') }}</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">{{ __('Hanya 3 Langkah Menuju Workshop Rapi') }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">1</div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('Input Pesanan dari WhatsApp') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Saat pelanggan menghubungi lewat WhatsApp, masukkan nama, jumlah, ukuran, tenggat, dan nominal DP ke OrderFlow dalam waktu kurang dari 30 detik.') }}</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">2</div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('Tim Bekerja lewat Kanban') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Operator workshop melihat antrean desain dan cetak. File desain tersimpan rapi di setiap pesanan tanpa takut hilang.') }}</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">3</div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('Kirim Notifikasi & Pelunasan') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ __('Selesai cetak? Klik tombol WhatsApp untuk mengabari pelanggan bahwa barang siap diambil beserta rincian sisa tagihan.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="harga" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ __('Investasi Sangat Terjangkau') }}</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">{{ __('Pilih Paket Sesuai Skala Workshop Anda') }}</h2>
                <p class="mt-3 text-base text-gray-600">{{ __('Semua pendaftaran baru otomatis mendapatkan 14 hari uji coba gratis Paket Pro. Tanpa ikatan kontrak.') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                <!-- Starter Plan -->
                <div class="rounded-3xl border border-gray-200 p-8 flex flex-col justify-between bg-white hover:border-gray-300 transition">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ __('UMKM Pemula') }}</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Starter</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Cocok untuk usaha sablon / cetak rumahan yang baru merintis.') }}</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">Rp 0</span>
                            <span class="text-xs font-bold text-gray-400">/ {{ __('selamanya') }}</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-600 font-medium">
                            @foreach(['Hingga 30 pesanan / bulan', '1 Akun Pengguna (Owner)', 'Papan Produksi Kanban', 'WhatsApp Template 1-Klik', 'Lacak Pesanan Publik'] as $feature)
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __($feature) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3 px-4 rounded-xl border border-gray-300 hover:border-gray-400 font-bold text-sm text-gray-800 transition">
                            {{ __('Mulai Gratis') }}
                        </a>
                    </div>
                </div>

                <!-- Pro Juragan Plan (POPULAR) -->
                <div class="rounded-3xl border-2 border-indigo-600 p-8 flex flex-col justify-between bg-gradient-to-b from-indigo-50/50 via-white to-white shadow-xl shadow-indigo-100 relative">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1 rounded-full shadow-sm">
                        {{ __('Paling Banyak Dipilih') }}
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">{{ __('Usaha Berkembang') }}</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Pro Juragan</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Solusi komplit untuk workshop aktif dengan staf kasir dan operator.') }}</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-indigo-600">Rp 49.000</span>
                            <span class="text-xs font-bold text-gray-500">/ {{ __('bulan') }}</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-700 font-semibold">
                            @foreach(['Unlimited Pesanan (Tanpa Batas)', 'Hingga 5 Akun Staf (Kasir & Operator)', 'Laporan Omset & Keuangan Lengkap', 'Export Data Rekap ke Excel / CSV', 'Cetak Invoice & Surat Jalan', 'Support Prioritas WhatsApp'] as $feature)
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __($feature) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition">
                            {{ __('Coba Gratis 14 Hari Sekarang') }}
                        </a>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="rounded-3xl border border-gray-200 p-8 flex flex-col justify-between bg-white hover:border-gray-300 transition">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ __('Skala Besar') }}</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Enterprise Sultan</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Untuk pabrik konveksi & percetakan dengan banyak divisi karyawan.') }}</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">Rp 99.000</span>
                            <span class="text-xs font-bold text-gray-400">/ {{ __('bulan') }}</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-600 font-medium">
                            @foreach(['Unlimited Pesanan', 'Unlimited Akun Staf (Tanpa Batas)', 'Semua Fitur Pro Juragan', 'Dedicated Account Manager', 'Bantuan Migrasi Data Khusus'] as $feature)
                                <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __($feature) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3 px-4 rounded-xl border border-gray-300 hover:border-gray-400 font-bold text-sm text-gray-800 transition">
                            {{ __('Pilih Enterprise') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-gray-50 border-t border-gray-200/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">{{ __('Pertanyaan Umum') }}</span>
                <h2 class="mt-4 text-3xl font-black text-gray-900 tracking-tight">{{ __('Sering Ditanyakan') }}</h2>
            </div>

            <div class="space-y-4" x-data="{ active: null }">
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>{{ __('Apakah saya perlu membayar biaya WhatsApp API tambahan?') }}</span>
                        <span x-text="active === 1 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 1" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        {{ __('Tidak. OrderFlow menggunakan tautan langsung wa.me yang gratis dan tidak memerlukan biaya langganan gateway bulanan.') }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>{{ __('Bagaimana jika masa uji coba 14 hari habis?') }}</span>
                        <span x-text="active === 2 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 2" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        {{ __('Data Anda tetap aman dan tidak akan dihapus. Anda dapat melanjutkan dengan paket gratis Starter atau memperpanjang paket Pro melalui transfer bank atau QRIS.') }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>{{ __('Apakah karyawan saya bisa melihat omset dan keuntungan toko?') }}</span>
                        <span x-text="active === 3 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 3" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        {{ __('Tidak. OrderFlow memiliki hak akses bertingkat. Operator hanya dapat melihat antrean teknis dan file desain, sedangkan Keuangan, Laporan, dan Omset khusus untuk Pemilik.') }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>{{ __('Apakah bisa diakses dari HP (smartphone)?') }}</span>
                        <span x-text="active === 4 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 4" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        {{ __('Tentu. OrderFlow responsif dan ringan dibuka melalui browser di ponsel Android maupun iPhone.') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="py-20 bg-indigo-900 bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-950 text-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight text-white">
                {{ __('Mulai Kelola Pesanan Custom Anda Hari Ini Secara Profesional') }}
            </h2>
            <p class="mt-4 text-base sm:text-lg text-indigo-100 font-medium max-w-2xl mx-auto">
                {{ __('Tinggalkan cara lama mencatat pesanan di buku atau chat WhatsApp yang gampang tenggelam.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-white text-indigo-700 hover:bg-indigo-50 font-black text-base shadow-xl transition transform hover:scale-105">
                    {{ __('Daftar Sekarang — Uji Coba Gratis 14 Hari') }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6 text-xs">
            <div class="flex items-center gap-3">
                <x-brand-mark class="w-7 h-7 shrink-0" />
                <span class="font-bold text-white tracking-tight text-sm">OrderFlow SaaS</span>
                <span>&copy; {{ date('Y') }} {{ __('Hak Cipta Dilindungi.') }}</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#fitur" class="hover:text-white transition">{{ __('Fitur') }}</a>
                <a href="#harga" class="hover:text-white transition">{{ __('Harga') }}</a>
                <a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('Syarat') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('Privasi') }}</a>
                <a href="{{ route('login') }}" class="hover:text-white transition">{{ __('Login Admin') }}</a>
            </div>
        </div>
    </footer>

</body>
</html>
