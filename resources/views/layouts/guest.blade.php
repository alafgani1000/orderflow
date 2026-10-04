<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrderFlow') }} — {{ __('Kelola Pesanan Custom & WhatsApp') }}</title>
        <x-favicon />

        <!-- Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { 
                font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                letter-spacing: -0.011em;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        @if($full ?? false)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4">
                <div class="mb-4">
                    <a href="/">
                        <x-application-logo />
                    </a>
                </div>

                <div class="w-full sm:max-w-md px-6 sm:px-8 py-6 bg-white shadow-xl border border-gray-100 rounded-3xl overflow-hidden">
                    {{ $slot }}
                </div>

                <div class="mt-6 text-center text-xs text-gray-400">
                    OrderFlow &copy; {{ date('Y') }} — {{ __('Solusi Kelola Pesanan WhatsApp') }}
                    <div class="mt-1.5 flex items-center justify-center gap-3">
                        <a href="{{ route('terms') }}" class="hover:text-indigo-600">{{ __('Syarat') }}</a>
                        <a href="{{ route('privacy') }}" class="hover:text-indigo-600">{{ __('Privasi') }}</a>
                    </div>
                </div>
            </div>
        @endif
    </body>
</html>
