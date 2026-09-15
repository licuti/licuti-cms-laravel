@props([
    'label' => '',
    'color' => 'gray',
])

@php
    $colorMap = [
        'green'  => 'text-bg-success bg-opacity-10 text-success border border-success border-opacity-25',
        'amber'  => 'text-bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
        'red'    => 'text-bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
        'blue'   => 'text-bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
        'default'=> 'text-bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
    ];
    $badgeClass = $colorMap[$color] ?? $colorMap['default'];
@endphp

<span {{ $attributes->merge(['class' => "badge fw-semibold {$badgeClass}"]) }}>
    {{ $label }}
</span>