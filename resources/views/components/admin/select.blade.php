@props([
    'disabled' => false,
    'required' => false,
    'size' => 'md'
])

@php
    $name = $attributes->get('name');
    $hasError = $name && $errors->has($name);
    
    $sizeClasses = [
        'sm' => 'form-select-sm',
        'md' => '',
    ];
    $sizeClass = $sizeClasses[$size] ?? '';
    
    $baseClasses = "form-select {$sizeClass}";
    if ($hasError) {
        $baseClasses .= ' is-invalid';
    }
@endphp

<select {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</select>
