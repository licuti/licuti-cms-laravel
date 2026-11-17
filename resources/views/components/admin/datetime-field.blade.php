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
        $display = $display->format('Y-m-d H:i');
    }

    if ($oldValue !== null) {
        $display = $oldValue;
    } elseif (($display === null || $display === '') && $defaultNow) {
        $display = now()->format('Y-m-d H:i');
    }
@endphp

<x-admin.form-group :label="$label" :name="$name" :description="$description">
    <div class="input-group input-group-{{ $size }}">
        <button
            type="button"
            class="btn btn-outline-secondary js-datetime-toggle"
            title="{{ __('Chọn ngày giờ') }}"
        >
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>
        <x-admin.input
            type="text"
            :name="$name"
            :size="$size"
            :value="$display"
            data-datetime-picker
            autocomplete="off"
            placeholder="dd/mm/yyyy hh:mm"
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
