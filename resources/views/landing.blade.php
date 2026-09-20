<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrderFlow — Software Manajemen Pesanan Sablon, Percetakan & Konveksi</title>
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
                    <a href="#fitur" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Fitur Utama</a>
                    <a href="#solusi" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Target Usaha</a>
                    <a href="#cara-kerja" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Cara Kerja</a>
                    <a href="#harga" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">Paket Harga</a>
                    <a href="#faq" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 transition">FAQ</a>
                </nav>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                            Masuk ke Dashboard
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-700 hover:text-indigo-600 px-4 py-2 transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                            Coba Gratis 14 Hari
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
            <a @click="mobileMenuOpen = false" href="#fitur" class="block py-2 text-sm font-semibold text-gray-700">Fitur Utama</a>
            <a @click="mobileMenuOpen = false" href="#solusi" class="block py-2 text-sm font-semibold text-gray-700">Target Usaha</a>
            <a @click="mobileMenuOpen = false" href="#cara-kerja" class="block py-2 text-sm font-semibold text-gray-700">Cara Kerja</a>
            <a @click="mobileMenuOpen = false" href="#harga" class="block py-2 text-sm font-semibold text-gray-700">Paket Harga</a>
            <a @click="mobileMenuOpen = false" href="#faq" class="block py-2 text-sm font-semibold text-gray-700">FAQ</a>
            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">Dashboard Toko</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-gray-200 text-gray-800 font-bold text-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">Coba Gratis 14 Hari</a>
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
                    SaaS Manajemen Usaha Custom #1 di Indonesia
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 tracking-tight leading-[1.15]">
                    Jangan Biarkan Pesanan, Deadline & Uang Muka <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Tercecer di WhatsApp!</span>
                </h1>

                <!-- Subtitle -->
                <p class="mt-6 text-lg sm:text-xl text-gray-600 leading-relaxed font-normal">
                    Solusi terpadu khusus pemilik <strong>sablon, percetakan, digital printing & konveksi</strong> untuk mengatur pesanan WhatsApp, papan produksi, catat DP pelunasan, hingga kirim kabar ke customer dalam 1-klik.
                </p>

                <!-- CTAs -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-base font-bold shadow-lg shadow-indigo-200 transition transform hover:-translate-y-0.5">
                        Mulai Uji Coba Gratis 14 Hari
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#cara-kerja" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl border border-gray-300 hover:border-gray-400 bg-white text-gray-700 text-base font-bold transition">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Lihat Alur Kerja
                    </a>
                </div>

                <!-- Guarantee / Trust Badges -->
                <div class="mt-6 flex flex-wrap items-center justify-center gap-6 text-xs text-gray-500 font-medium">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Tanpa Kartu Kredit
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Setup 1 Menit Langsung Pakai
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Zero-Cost WhatsApp (wa.me)
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
                                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Pesanan Aktif</p>
                                <p class="text-2xl font-black text-indigo-600 mt-1">28</p>
                                <p class="text-xs text-gray-500 mt-0.5">Sedang dikerjakan</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Jatuh Tempo Hari Ini</p>
                                <p class="text-2xl font-black text-amber-600 mt-1">4</p>
                                <p class="text-xs text-gray-500 mt-0.5">Prioritas selesai</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Terlambat (Overdue)</p>
                                <p class="text-2xl font-black text-rose-600 mt-1">1</p>
                                <p class="text-xs text-gray-500 mt-0.5">Perlu perhatian</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">Omset Bulan Ini</p>
                                <p class="text-2xl font-black text-emerald-600 mt-1">Rp 34,8 Jt</p>
                                <p class="text-xs text-gray-500 mt-0.5">100% Tercatat</p>
                            </div>
                        </div>

                        <!-- Orders sample row -->
                        <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <span class="font-bold text-sm text-gray-800">Daftar Pengerjaan Workshop Terdekat</span>
                                <span class="text-xs bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-full">Live Synchronized</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs py-2 border-b border-gray-50">
                                <div>
                                    <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">ORD-0042</span>
                                    <span class="font-bold text-gray-800 ml-2">50 Pcs Kaos Sablon DTF Komunitas Motor</span>
                                    <p class="text-gray-400 mt-0.5">Budi Santoso &bull; WhatsApp: 081234567890</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded-lg">Sedang Produksi</span>
                                    <span class="text-emerald-600 font-bold">DP: Rp 750.000 (Sisa Rp 500k)</span>
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
            <p class="text-center text-xs font-bold uppercase tracking-widest text-indigo-600 mb-3">Dirancang Spesifik Untuk Industri Custom</p>
            <h2 class="text-center text-2xl sm:text-3xl font-black text-gray-900 mb-10">Cocok untuk Berbagai Tipe Usaha Workshop Anda</h2>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl mb-3">👕</div>
                    <h3 class="font-bold text-sm text-gray-900">Sablon & DTF</h3>
                    <p class="text-xs text-gray-500 mt-1">Kaos, totebag, hoodie, jersey & merchandise.</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl mb-3">🖨️</div>
                    <h3 class="font-bold text-sm text-gray-900">Digital Printing</h3>
                    <p class="text-xs text-gray-500 mt-1">Spanduk flexi, banner, stiker & poster.</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl mb-3">🧵</div>
                    <h3 class="font-bold text-sm text-gray-900">Konveksi Seragam</h3>
                    <p class="text-xs text-gray-500 mt-1">Kemeja PDH, jaket varsity, celana & rompi.</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl mb-3">💌</div>
                    <h3 class="font-bold text-sm text-gray-900">Percetakan & Undangan</h3>
                    <p class="text-xs text-gray-500 mt-1">Undangan pernikahan, souvenir & packaging.</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 text-center hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl mb-3">☕</div>
                    <h3 class="font-bold text-sm text-gray-900">Souvenir & Gift</h3>
                    <p class="text-xs text-gray-500 mt-1">Mug custom, tumbler grafir, gantungan kunci.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Fitur Lengkap MVP to Scale</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">Semua yang Dibutuhkan Workshop Anda Ada di Sini</h2>
                <p class="mt-3 text-base text-gray-600">Didesain dari pengalaman nyata ribuan percetakan yang pusing melacak order dari chat WA.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Dashboard Anti Terlambat</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Pantau pesanan yang harus selesai hari ini dan yang terlambat (*overdue*). Jangan sampai kena komplain customer karena deadline terlewat.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">WhatsApp 1-Klik (Zero-Cost API)</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Kirim kabar status produksi, tanda terima pembayaran DP, dan notifikasi pesanan selesai langsung ke WA customer dengan template otomatis rapi.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Papan Produksi Kanban</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Geser kartu pesanan dari Menunggu Desain &rarr; Disetujui &rarr; Produksi &rarr; Selesai &rarr; Diambil. Seluruh tim workshop tahu apa yang harus dikerjakan.</p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Lacak Mandiri Pelanggan (Self-Tracking)</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Berikan link publik khusus (`/lacak/token`) kepada pembeli agar mereka bisa melihat progres cetak dan sisa bayar tanpa terus-menerus chat admin.</p>
                </div>

                <!-- Feature 5 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Pencatatan DP & Invoice Resmi</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Catat uang muka (DP) dan cicilan secara terperinci. Cetak invoice format bersih dengan logo toko Anda siap kirim atau print surat jalan.</p>
                </div>

                <!-- Feature 6 -->
                <div class="p-6 rounded-2xl border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all group bg-white">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Laporan Omset & Export Excel</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Rekap pendapatan, piutang belum lunas, dan metode bayar (Cash/Transfer/QRIS). Export ke CSV Excel 1-klik yang kebal terhadap injection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="cara-kerja" class="py-20 bg-gray-50 border-t border-gray-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Alur Kerja Praktis</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">Hanya 3 Langkah Menuju Workshop Rapi</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">1</div>
                    <h3 class="text-xl font-bold text-gray-900">Input Order dari WhatsApp</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Saat customer chat di WA, masukkan nama, qty, ukuran, deadline, dan nominal DP ke OrderFlow dalam waktu kurang dari 30 detik.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">2</div>
                    <h3 class="text-xl font-bold text-gray-900">Tim Kerjakan via Kanban</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Operator workshop melihat antrean desain dan cetak. Desain file (.ai/.psd/.pdf) tersimpan rapi di setiap pesanan tanpa takut hilang.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-200/80 shadow-xs relative">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-xl flex items-center justify-center mb-6">3</div>
                    <h3 class="text-xl font-bold text-gray-900">Kirim Notif & Pelunasan</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Selesai cetak? Klik tombol WA untuk kabari customer bahwa barang siap diambil beserta rincian sisa tagihan yang harus dilunasi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="harga" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Investasi Sangat Terjangkau</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">Pilih Paket Sesuai Skala Workshop Anda</h2>
                <p class="mt-3 text-base text-gray-600">Semua pendaftaran baru otomatis mendapatkan <strong>14 Hari Uji Coba Gratis Paket Pro</strong>. Tanpa ikatan kontrak.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                <!-- Starter Plan -->
                <div class="rounded-3xl border border-gray-200 p-8 flex flex-col justify-between bg-white hover:border-gray-300 transition">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-100 px-3 py-1 rounded-full">UMKM Pemula</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Starter</h3>
                        <p class="text-xs text-gray-500 mt-1">Cocok untuk usaha sablon / cetak rumahan yang baru merintis.</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">Rp 0</span>
                            <span class="text-xs font-bold text-gray-400">/ selamanya</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-600 font-medium">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Hingga 30 pesanan / bulan</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 1 Akun Pengguna (Owner)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Papan Produksi Kanban</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> WhatsApp Template 1-Klik</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Lacak Pesanan Publik</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3 px-4 rounded-xl border border-gray-300 hover:border-gray-400 font-bold text-sm text-gray-800 transition">
                            Mulai Gratis
                        </a>
                    </div>
                </div>

                <!-- Pro Juragan Plan (POPULAR) -->
                <div class="rounded-3xl border-2 border-indigo-600 p-8 flex flex-col justify-between bg-gradient-to-b from-indigo-50/50 via-white to-white shadow-xl shadow-indigo-100 relative">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1 rounded-full shadow-sm">
                        Paling Banyak Dipilih
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">Usaha Berkembang</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Pro Juragan</h3>
                        <p class="text-xs text-gray-500 mt-1">Solusi komplit untuk workshop aktif dengan staf kasir dan operator.</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-indigo-600">Rp 49.000</span>
                            <span class="text-xs font-bold text-gray-500">/ bulan</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-700 font-semibold">
                            <li class="flex items-center gap-2 text-indigo-900 font-bold"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Pesanan (Tanpa Batas)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Hingga 5 Akun Staf (Kasir & Operator)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Laporan Omset & Keuangan Lengkap</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Export Data Rekap ke Excel / CSV</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Cetak Invoice & Surat Jalan</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Support Prioritas WhatsApp</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition">
                            Coba Gratis 14 Hari Sekarang
                        </a>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="rounded-3xl border border-gray-200 p-8 flex flex-col justify-between bg-white hover:border-gray-300 transition">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Skala Besar</span>
                        <h3 class="text-2xl font-black text-gray-900 mt-4">Enterprise Sultan</h3>
                        <p class="text-xs text-gray-500 mt-1">Untuk pabrik konveksi & percetakan dengan banyak divisi karyawan.</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">Rp 99.000</span>
                            <span class="text-xs font-bold text-gray-400">/ bulan</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-xs text-gray-600 font-medium">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Pesanan</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited Akun Staf (Tanpa Batas)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Semua Fitur Pro Juragan</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Dedicated Account Manager</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Bantuan Migrasi Data Khusus</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center py-3 px-4 rounded-xl border border-gray-300 hover:border-gray-400 font-bold text-sm text-gray-800 transition">
                            Pilih Enterprise
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
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Pertanyaan Umum</span>
                <h2 class="mt-4 text-3xl font-black text-gray-900 tracking-tight">Sering Ditanyakan</h2>
            </div>

            <div class="space-y-4" x-data="{ active: null }">
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>Apakah saya perlu membayar biaya WhatsApp API tambahan?</span>
                        <span x-text="active === 1 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 1" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        Tidak sama sekali! OrderFlow menggunakan teknologi tautan langsung `wa.me` yang 100% gratis, aman dari banned nomor, dan tidak memerlukan biaya langganan gateway bulanan yang mahal.
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>Bagaimana jika masa uji coba 14 hari habis?</span>
                        <span x-text="active === 2 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 2" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        Data Anda tetap 100% aman dan tidak akan dihapus. Anda dapat melanjutkan dengan paket gratis Starter atau memperpanjang paket Pro seharga Rp 49.000/bulan melalui transfer bank atau QRIS.
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>Apakah karyawan saya bisa melihat omset dan keuntungan toko?</span>
                        <span x-text="active === 3 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 3" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        Tidak. OrderFlow memiliki sistem hak akses bertingkat (*Role-based*). Karyawan dengan peran Operator Workshop hanya dapat melihat antrean pesanan teknis dan file desain, sedangkan menu Keuangan, Laporan, dan Omset terkunci khusus untuk Pemilik (Owner).
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full text-left px-6 py-4 font-bold text-sm text-gray-900 flex justify-between items-center">
                        <span>Apakah bisa diakses dari HP (smartphone)?</span>
                        <span x-text="active === 4 ? '−' : '+'" class="text-lg text-indigo-600 font-bold"></span>
                    </button>
                    <div x-show="active === 4" class="px-6 pb-4 text-xs text-gray-600 leading-relaxed">
                        Tentu saja! Tampilan OrderFlow sepenuhnya responsif dan sangat ringan dibuka melalui browser Google Chrome atau Safari di ponsel Android maupun iPhone.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="py-20 bg-indigo-900 bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-950 text-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight text-white">
                Mulai Kelola Pesanan Custom Anda Hari Ini Secara Profesional
            </h2>
            <p class="mt-4 text-base sm:text-lg text-indigo-100 font-medium max-w-2xl mx-auto">
                Tinggalkan cara lama mencatat pesanan di buku atau chat WhatsApp yang gampang tenggelam.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-white text-indigo-700 hover:bg-indigo-50 font-black text-base shadow-xl transition transform hover:scale-105">
                    Daftar Sekarang — Uji Coba Gratis 14 Hari
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
                <span>&copy; {{ date('Y') }} Hak Cipta Dilindungi.</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#fitur" class="hover:text-white transition">Fitur</a>
                <a href="#harga" class="hover:text-white transition">Harga</a>
                <a href="{{ route('login') }}" class="hover:text-white transition">Login Admin</a>
            </div>
        </div>
    </footer>

</body>
</html>
