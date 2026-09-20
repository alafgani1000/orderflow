@props(['value' => '', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-gray-700 mb-1.5']) }}>
    {!! $value !!}{{ $slot }}
    @if($required)
        <span class="text-red-500 ms-0.5">*</span>
    @endif
</label>
