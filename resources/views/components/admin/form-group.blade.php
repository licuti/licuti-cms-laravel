@props([
    'label' => null, 
    'name' => null, 
    'description' => null, 
    'required' => false
])

<div class="mb-3">
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="form-label small fw-medium">
            {{ $label }} @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    {{ $slot }}

    @if($description)
        <div class="form-text">{{ $description }}</div>
    @endif

    @if($name && $errors->has($name))
        <div class="invalid-feedback d-block">{{ $errors->first($name) }}</div>
    @endif
</div>
