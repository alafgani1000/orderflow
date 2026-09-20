@props(['alt' => 'OrderFlow'])

<img
    src="{{ asset('images/orderflow-mark.png') }}"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => 'object-contain']) }}
>
