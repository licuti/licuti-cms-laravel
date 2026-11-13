@props([
    'image' => null,
    'alt' => '',
    'size' => '2.5rem',
    'shape' => 'rounded',
])

@php
    $shapeClass = $shape === 'circle' ? 'rounded-circle' : 'rounded';
@endphp

@if($image)
    <img src="{{ $image }}" alt="{{ $alt }}" class="{{ $shapeClass }} border object-fit-cover"
         style="width:{{ $size }};height:{{ $size }};">
@else
    <span class="{{ $shapeClass }} border bg-body-tertiary text-body-secondary d-inline-flex align-items-center justify-content-center"
          style="width:{{ $size }};height:{{ $size }};">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </span>
@endif
