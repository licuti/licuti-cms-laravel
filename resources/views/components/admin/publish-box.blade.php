@props([
    'title'           => null,
    'statuses'        => [],
    'status'          => null,
    'statusName'      => 'status',
    'statusLabel'     => null,
    'showStatuses'    => true,
    'publishedAt'     => null,
    'showPublishedAt' => true,
    'showFeatured'    => false,
    'featured'        => false,
    'featuredLabel'   => null,
    'indexRoute'      => null,
    'saveLabel'       => null,
    'defaultNow'      => false,
])

@php
    $title         = $title ?: __('Xuất bản');
    $statusLabel   = $statusLabel ?: __('Trạng thái');
    $featuredLabel = $featuredLabel ?: __('Đánh dấu nổi bật');
    $saveLabel     = $saveLabel ?: __('Lưu');
    $statusVal     = $status instanceof \BackedEnum ? $status->value : (string) ($status !== null ? $status : 'draft');
    $current       = old($statusName, $statusVal);
@endphp

<x-admin.card :title="$title">
    @if($showStatuses && !empty($statuses))
        <x-admin.form-group :label="$statusLabel" :name="$statusName">
            <x-admin.select :name="$statusName" size="sm">
                @foreach($statuses as $val => $lbl)
                    <option value="{{ $val }}" @selected((string)$current === (string)$val)>{{ $lbl }}</option>
                @endforeach
            </x-admin.select>
        </x-admin.form-group>
    @endif

    @if($showPublishedAt)
        <x-admin.datetime-field
            name="published_at"
            :label="__('Ngày xuất bản')"
            :value="$publishedAt"
            description="{{ __('Bấm × để gỡ ngày đăng.') }}"
            :default-now="$defaultNow"
        />
    @endif

    @if($showFeatured)
        <x-admin.form-group class="mb-0">
            <x-admin.toggle
                name="is_featured"
                :label="$featuredLabel"
                :checked="old('is_featured', $featured)"
            />
        </x-admin.form-group>
    @endif

    {{ $slot }}

    <div class="d-flex gap-2 pt-3 mt-3 border-top">
        @if($indexRoute)
            <x-admin.button href="{{ $indexRoute }}" variant="outline-secondary" size="sm" class="flex-grow-1">
                {{ __('Quay lại') }}
            </x-admin.button>
        @endif

        <x-admin.button type="submit" name="submit_action" value="save" variant="primary" size="sm" class="flex-grow-1">
            {{ $saveLabel }}
        </x-admin.button>

        <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="secondary" size="sm" class="flex-grow-1">
            {{ __('Lưu & Sửa') }}
        </x-admin.button>
    </div>
</x-admin.card>
