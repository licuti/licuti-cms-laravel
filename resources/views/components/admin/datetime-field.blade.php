@props([
    'name',
    'label'       => null,
    'value'       => null,
    'size'        => 'sm',
    'description' => null,
    'clearable'   => true,
    'defaultNow'  => false,
])

@php
    // Support both formats used in old() lookups.
    $oldKey   = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $oldValue = old($oldKey);

    $display = $value;
    if ($display instanceof \DateTimeInterface) {
        $display = $display->format('Y-m-d\TH:i');
    }

    if ($oldValue !== null) {
        $display = $oldValue;
    } elseif (($display === null || $display === '') && $defaultNow) {
        $display = now()->format('Y-m-d\TH:i');
    }
@endphp

<x-admin.form-group :label="$label" :name="$name" :description="$description">
    <div class="input-group input-group-{{ $size }}">
        <x-admin.input
            type="datetime-local"
            :name="$name"
            :size="$size"
            :value="$display"
        />
        @if($clearable)
            <button
                type="button"
                class="btn btn-outline-secondary js-datetime-clear"
                title="{{ __('Dọn giá trị') }}"
            >&times;</button>
        @endif
    </div>
</x-admin.form-group>

@once
@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-datetime-clear');
        if (!btn) return;
        const group = btn.closest('.input-group');
        const input = group && group.querySelector('input[type="datetime-local"]');
        if (input) input.value = '';
    });
</script>
@endpush
@endonce
