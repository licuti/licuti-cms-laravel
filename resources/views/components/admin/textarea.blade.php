@props([
    'disabled' => false,
    'required' => false,
    'size' => 'md',
    'rows' => 4,
])

@php
    $name = preg_replace('/\[.*?\]/', '', $attributes->get('name', ''));
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

<textarea 
    {{ $disabled ? 'disabled' : '' }} 
    {{ $required ? 'required' : '' }} 
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => $baseClasses]) }}
>{{ $slot }}</textarea>
