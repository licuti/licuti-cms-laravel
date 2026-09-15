@props([
    'variant' => 'primary',
    'size' => 'sm',
    'type' => 'button',
    'href' => null
])
@php
    $variantMap = [
        'primary'   => 'btn-primary',
        'secondary' => 'btn-secondary',
        'success'   => 'btn-success',
        'danger'    => 'btn-danger',
        'warning'   => 'btn-warning',
        'info'      => 'btn-info',
        'light'     => 'btn-light',
        'dark'      => 'btn-dark',
        'link'      => 'btn-link',
        'outline-primary'   => 'btn-outline-primary',
        'outline-secondary' => 'btn-outline-secondary',
        'outline-success'   => 'btn-outline-success',
        'outline-danger'    => 'btn-outline-danger',
        'outline-warning'   => 'btn-outline-warning',
        'outline-info'      => 'btn-outline-info',
        'outline-light'     => 'btn-outline-light',
        'outline-dark'      => 'btn-outline-dark',
    ];
    $btnClass = $variantMap[$variant] ?? $variantMap['primary'];

    $sizeMap = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['sm'];
    $classes = "btn {$btnClass} {$sizeClass} d-inline-flex align-items-center justify-content-center gap-1";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
