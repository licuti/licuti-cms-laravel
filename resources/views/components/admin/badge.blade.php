@props([
    'label' => '',
    'color' => null,
])

@php
    $colorMap = [
        'primary'   => 'text-bg-primary',
        'secondary' => 'text-bg-secondary',
        'success'   => 'text-bg-success',
        'danger'    => 'text-bg-danger',
        'warning'   => 'text-bg-warning',
        'info'      => 'text-bg-info',
        'light'     => 'text-bg-light',
        'dark'      => 'text-bg-dark',
    ];
    $resolvedColor = $color ?? $attributes->get('variant') ?? 'secondary';
    $badgeClass = $colorMap[$resolvedColor] ?? $colorMap['secondary'];
@endphp

<span {{ $attributes->merge(['class' => "badge {$badgeClass}"]) }}>
    {{ !empty($label) ? $label : $slot }}
</span>