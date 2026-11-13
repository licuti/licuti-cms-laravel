@props([
    'align' => 'left',
    'padding' => ''
])

@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start'
    };
@endphp

<th {{ $attributes->merge(['class' => "{$padding} small {$alignClass}"]) }}>
    {{ $slot }}
</th>