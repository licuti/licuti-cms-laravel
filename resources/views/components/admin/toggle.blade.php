@props([
    'name',
    'id'          => null,
    'label'       => null,
    'description' => null,
    'checked'     => false,
    'value'       => '1',
    'disabled'    => false,
    'readonly'    => false,
    'size'        => 'md',
    'color'       => 'blue',
    'error'       => null,
])

@php
    $isReadonly = $disabled || $readonly;
@endphp

<div {{ $attributes->whereStartsWith('class')->merge(['class' => $isReadonly ? 'opacity-50' : '']) }}>
    <label class="d-inline-flex align-items-center {{ $isReadonly ? '' : '' }}" @if($isReadonly) style="pointer-events:none;" @endif>
        @unless($isReadonly)
            <input type="hidden" name="{{ $name }}" value="0">
        @endunless

        <div class="form-check form-switch mb-0">
            <input
                type="checkbox"
                name="{{ $name }}"
                value="{{ $value }}"
                role="switch"
                @if($id) id="{{ $id }}" @endif
                @if($description && $id) aria-describedby="{{ $id }}-description" @endif
                class="form-check-input"
                @checked($checked)
                @disabled($isReadonly)
                {{ $attributes->whereDoesntStartWith('class')->except(['name', 'value', 'id']) }}
            >
        </div>

        @if($label || $description || $error)
            <div class="ms-2 user-select-none">
                @if($label)
                    <span class="d-block fw-medium small">{{ $label }}</span>
                @endif
                @if($description)
                    <p @if($id) id="{{ $id }}-description" @endif class="text-body-secondary small mb-0 mt-1" style="font-size:0.75rem;">{{ $description }}</p>
                @endif
                @if($error)
                    <p class="text-danger small mb-0 mt-1 fw-medium" style="font-size:0.75rem;">{{ $error }}</p>
                @endif
            </div>
        @endif
    </label>
</div>
