@props([
    'title' => 'Tidak ada data',
    'description' => null,
    'actionText' => null,
    'actionUrl' => null,
])

<div class="text-center py-10 px-6">
    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
    </div>
    <h3 class="text-sm font-bold text-gray-800 mb-1">{{ $title }}</h3>
    @if($description)
        <p class="text-xs text-gray-500 max-w-xs mx-auto leading-relaxed">{{ $description }}</p>
    @endif
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
            {{ $actionText }}
        </a>
    @endif
</div>
