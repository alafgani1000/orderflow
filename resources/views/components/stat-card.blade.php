@props([
    'title',
    'value',
    'subtitle' => null,
    'color' => 'indigo',
    'href' => null,
])

@php
    $isZero = (int) $value === 0;
    $cfg = match($color) {
        'amber'   => ['ring' => 'ring-amber-200',  'icon' => 'bg-amber-50 text-amber-500',   'num' => $isZero ? 'text-gray-400' : 'text-amber-600', 'link' => 'text-amber-600'],
        'rose'    => ['ring' => 'ring-rose-200',   'icon' => 'bg-rose-50 text-rose-500',     'num' => $isZero ? 'text-gray-400' : 'text-rose-600',  'link' => 'text-rose-600'],
        'emerald' => ['ring' => 'ring-emerald-200','icon' => 'bg-emerald-50 text-emerald-600','num' => 'text-gray-900',                              'link' => 'text-emerald-600'],
        default   => ['ring' => 'ring-indigo-200', 'icon' => 'bg-indigo-50 text-indigo-600', 'num' => 'text-gray-900',                              'link' => 'text-indigo-600'],
    };
@endphp

<div class="bg-white rounded-2xl p-5 border border-gray-200/80 hover:border-gray-300 shadow-xs transition-shadow hover:shadow-sm flex flex-col gap-3">
    <div class="flex items-start justify-between">
        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $title }}</p>
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $cfg['icon'] }}">
            {{ $slot }}
        </div>
    </div>

    <div>
        <p class="text-3xl font-black leading-none {{ $cfg['num'] }}">{{ $value }}</p>
        @if($subtitle)
            <p class="text-xs text-gray-400 mt-1 leading-snug">{{ $subtitle }}</p>
        @endif
    </div>

    @if($href)
        <div class="mt-auto pt-3 border-t border-gray-100">
            <a href="{{ $href }}" class="inline-flex items-center gap-1 text-xs font-semibold {{ $cfg['link'] }} hover:underline group">
                {{ __('Lihat daftar') }}
                <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    @endif
</div>
