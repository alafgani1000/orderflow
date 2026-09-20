<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="referrer" content="no-referrer-when-downgrade">

        <title>{{ isset($title) ? $title . ' — OrderFlow' : 'OrderFlow — Kelola Pesanan WhatsApp' }}</title>
        <x-favicon />

        <!-- Inter Font -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap">
        </noscript>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col">

        @include('layouts.navigation')

        <!-- Flash Messages -->
        @if (session('success') || session('error') || $errors->any())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-5">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-3 flex items-center justify-between px-4 py-3 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-xs transition" role="alert">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-5 h-5 shrink-0 flex items-center justify-center text-emerald-600">
                                <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium text-emerald-900">{{ session('success') }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ms-3">
                            @if (session('wa_status_url') || session('wa_payment_url'))
                                @php
                                    $shareUrl = session('wa_status_url') ?? session('wa_payment_url');
                                @endphp
                                <a href="{{ $shareUrl }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg transition shrink-0">
                                    <svg class="w-3.5 h-3.5" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                    </svg>
                                    Kirim via WhatsApp
                                </a>
                            @endif
                            <button type="button" @click="show = false" class="p-1 text-emerald-500 hover:text-emerald-700 rounded-lg hover:bg-emerald-100 transition" title="Tutup">
                                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-3 flex items-center justify-between px-4 py-3 text-sm text-red-800 bg-red-50 border border-red-200 rounded-xl shadow-xs transition" role="alert">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-5 h-5 shrink-0 flex items-center justify-center text-red-600">
                                <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium text-red-900">{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="p-1 text-red-500 hover:text-red-700 rounded-lg hover:bg-red-100 transition shrink-0 ms-3" title="Tutup">
                            <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-3 px-4 py-3 text-sm text-red-800 bg-red-50 border border-red-200 rounded-xl shadow-xs" role="alert">
                        <p class="font-semibold mb-1.5">Harap periksa kembali formulir Anda:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Page Heading -->
        @isset($header)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-6 pb-2">
                {{ $header }}
            </div>
        @endisset

        <!-- Page Content -->
        <main class="flex-1 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-5">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-400">
            <strong class="text-gray-500">OrderFlow</strong> &copy; {{ date('Y') }} — Solusi Kelola Pesanan Custom &amp; WhatsApp
        </footer>

        <!-- Floating Toast Notification (Always Visible on Action) -->
        @if (session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 8000)"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-white border border-emerald-300/80 rounded-2xl shadow-2xl p-4 flex items-start gap-3.5 ring-1 ring-emerald-500/20"
                 style="display: none;"
                 role="status">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Berhasil Disimpan</p>
                    </div>
                    <p class="text-xs font-semibold text-gray-900 mt-1 leading-snug">{{ session('success') }}</p>
                    @if (session('wa_status_url') || session('wa_payment_url'))
                        @php
                            $toastShareUrl = session('wa_status_url') ?? session('wa_payment_url');
                        @endphp
                        <div class="mt-3">
                            <a href="{{ $toastShareUrl }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                <svg class="w-3.5 h-3.5" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Kirim via WhatsApp</span>
                            </a>
                        </div>
                    @endif
                </div>
                <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition shrink-0" title="Tutup">
                    <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Top Navigation Loading Bar (Instant 0ms Feedback) -->
        <div id="page-loading-bar" class="fixed top-0 left-0 h-[3px] w-0 bg-gradient-to-r from-indigo-500 via-indigo-400 to-indigo-600 z-[99999] pointer-events-none opacity-0 shadow-xs" style="transition: width 0.3s ease, opacity 0.2s ease;"></div>

        <script>
            (function() {
                const bar = document.getElementById('page-loading-bar');
                let progressTimer;

                function startProgress() {
                    if (!bar) return;
                    clearInterval(progressTimer);
                    bar.style.opacity = '1';
                    bar.style.width = '30%';

                    let current = 30;
                    progressTimer = setInterval(function() {
                        if (current < 85) {
                            current += Math.random() * 12;
                            if (current > 85) current = 85;
                            bar.style.width = current + '%';
                        }
                    }, 180);
                }

                function stopProgress() {
                    if (!bar) return;
                    clearInterval(progressTimer);
                    bar.style.width = '100%';
                    setTimeout(function() {
                        bar.style.opacity = '0';
                        setTimeout(function() {
                            bar.style.width = '0%';
                        }, 250);
                    }, 150);
                }

                // Listen to clicks on navigation and internal links
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href) return;

                    // Ignore external, hash anchors, javascript calls, mailto, tel, or special clicks
                    if (
                        href.startsWith('#') ||
                        href.startsWith('javascript:') ||
                        href.startsWith('mailto:') ||
                        href.startsWith('tel:') ||
                        link.getAttribute('target') === '_blank' ||
                        link.hasAttribute('download') ||
                        e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ||
                        e.defaultPrevented
                    ) {
                        return;
                    }

                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin === window.location.origin) {
                            startProgress();
                        }
                    } catch (err) {}
                }, true);

                // Show progress on form submit (e.g. Logout, Filter, Save)
                document.addEventListener('submit', function(e) {
                    if (!e.defaultPrevented) {
                        startProgress();
                    }
                });

                // Reset progress on page show (including back/forward navigation)
                window.addEventListener('pageshow', function() {
                    stopProgress();
                });
            })();
        </script>
    </body>
</html>
