@props([
    'name'          => null,
    'id'            => null,
    'options'       => [],    // Collection cây đã build sẵn (_children), hoặc flat list
    'selected'      => [],    // array ID đã chọn
    'type'          => 'checkbox',
    'maxHeight'     => '300px',
    'valueField'    => 'id',
    'labelField'    => 'name',
])

@php
    // Chuẩn hóa name: checkbox cần [], radio không cần
    if ($type === 'checkbox' && $name && !str_ends_with($name, '[]')) {
        $inputName = $name . '[]';
    } elseif ($type === 'radio') {
        $inputName = rtrim($name ?? '', '[]');
    } else {
        $inputName = $name ?? '';
    }

    // Chuẩn hóa selected về array thuần
    $selectedArray = match(true) {
        is_array($selected)                                           => $selected,
        $selected instanceof \Illuminate\Support\Collection          => $selected->values()->all(),
        $selected !== null && $selected !== ''                        => [(int) $selected],
        default                                                       => [],
    };

    // ID cho component để không dùng uniqid() chậm/trùng
    $inputId = $id ?? 'tree-' . \Illuminate\Support\Str::slug($name ?? 'cb') . '-' . \Illuminate\Support\Str::random(4);

    // Tách riêng attribute cho wrapper (class, style) và input (disabled, required, data-*)
    $wrapperAttributes = $attributes->only(['class', 'style']);
    $inputAttributes = $attributes->except(['class', 'style']);
@endphp

<div
    id="{{ $inputId }}"
    {{ $wrapperAttributes->merge(['class' => 'border rounded p-2 overflow-auto bg-body-tertiary']) }}
    style="max-height: {{ $maxHeight }};"
>
    @if(collect($options)->isEmpty())
        <div class="text-muted small p-2">-- Trống --</div>
    @else
        <ul class="list-unstyled mb-0">
            @include('components.admin.partials.tree-node', [
                'nodes'           => collect($options),
                'inputName'       => $inputName,
                'inputId'         => $inputId,
                'inputAttributes' => $inputAttributes,
                'type'            => $type,
                'selectedArray'   => $selectedArray,
                'valueField'      => $valueField,
                'labelField'      => $labelField,
                'depth'           => 0,
            ])
        </ul>
    @endif
</div>
