@props([
    'statuses'        => [],
    'status'          => null,
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
    $featuredLabel = $featuredLabel ?: __('Đánh dấu nổi bật');
    $saveLabel     = $saveLabel ?: __('Lưu');
    $statusVal     = $status instanceof \BackedEnum ? $status->value : (string) ($status ?: 'draft');
    $current       = old('status', $statusVal);
@endphp

<x-admin.card title="{{ __('Xuất bản') }}">
    <x-admin.form-group :label="__('Trạng thái')" name="status">
        <x-admin.select name="status" size="sm">
            @foreach($statuses as $val => $lbl)
                <option value="{{ $val }}" @selected($current === $val)>{{ $lbl }}</option>
            @endforeach
        </x-admin.select>
    </x-admin.form-group>

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
