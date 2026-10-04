@php
    $currentLocale = app()->getLocale();
    $locales = config('app.available_locales');
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button type="button"
            @click="open = !open"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-600 shadow-2xs transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
            :aria-expanded="open.toString()"
            aria-haspopup="menu"
            title="{{ __('Change language') }}">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.05 4.05A18.02 18.02 0 016 18m4.05-8.95A18.02 18.02 0 0114 18m7-7h-6m3-3v6"/>
        </svg>
        <span>{{ strtoupper($currentLocale) }}</span>
        <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
    </button>

    <div x-cloak
         x-show="open"
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-gray-200 bg-white p-1 shadow-xl"
         role="menu">
        @foreach($locales as $locale => $name)
            <form method="POST" action="{{ route('locale.update', $locale) }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-semibold transition {{ $currentLocale === $locale ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                        role="menuitem">
                    <span>{{ $name }}</span>
                    @if($currentLocale === $locale)
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
