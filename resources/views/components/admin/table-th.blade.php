@props([
    'align' => 'left',
    'padding' => 'px-3'
])

@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start'
    };
@endphp

<th {{ $attributes->merge(['class' => "{$padding} small tracking-wider {$alignClass}"]) }}>
    {{ $slot }}
</th>
