@props(['title', 'version'])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — OrderFlow</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur-md">
        <div class="max-w-5xl mx-auto h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <x-brand-mark class="w-8 h-8 shrink-0" />
                <span class="font-black tracking-tight text-lg">Order<span class="text-indigo-600">Flow</span></span>
            </a>
            <div class="flex items-center gap-3">
                <x-locale-switcher />
                <a href="{{ route('login') }}" class="hidden sm:inline-flex px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    {{ __('Masuk') }}
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <article class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 sm:px-10 py-8 sm:py-10 border-b border-slate-100 bg-gradient-to-br from-indigo-50/70 to-white">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 mb-5">
                    <span aria-hidden="true">←</span> {{ __('legal.common.back_home') }}
                </a>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-950">{{ $title }}</h1>
                <p class="mt-2 text-xs text-slate-500">
                    {{ __('legal.common.effective_date', ['date' => \Carbon\Carbon::parse($version)->translatedFormat('d F Y')]) }}
                </p>
            </div>
            <div class="px-6 sm:px-10 py-8 sm:py-10">
                {{ $slot }}
            </div>
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-7 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} {{ config('orderflow.company.name') }}</span>
            <div class="flex flex-wrap justify-center gap-5">
                <a href="{{ route('terms') }}" class="hover:text-indigo-600 transition">{{ __('legal.common.terms') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-indigo-600 transition">{{ __('legal.common.privacy') }}</a>
                <a href="mailto:{{ config('orderflow.company.support_email') }}" class="hover:text-indigo-600 transition">{{ __('legal.common.contact') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
