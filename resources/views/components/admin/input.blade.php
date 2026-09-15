@props([
    'type' => 'text',
    'disabled' => false,
    'required' => false,
    'size' => 'md'
])

@php
    $name = $attributes->get('name');
    $hasError = $name && $errors->has($name);
    
    $sizeClasses = [
        'sm' => 'form-control-sm',
        'md' => '',
    ];
    $sizeClass = $sizeClasses[$size] ?? '';
    
    $baseClasses = "form-control {$sizeClass}";
    if ($hasError) {
        $baseClasses .= ' is-invalid';
    }
@endphp

@if(isset($prefix) || isset($suffix))
    <div class="input-group">
        @if(isset($prefix))
            <span class="input-group-text bg-body-secondary border-end-0">{{ $prefix }}</span>
        @endif

        <input type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => $baseClasses]) }}>

        @if(isset($suffix))
            <span class="input-group-text bg-body-secondary border-start-0">{{ $suffix }}</span>
        @endif
    </div>
@else
    <input type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => $baseClasses]) }}>
@endif
