@props([
    'title'            => null,
    'statuses'         => [],
    'status'           => null,
    'statusName'       => 'status',
    'statusLabel'      => null,
    'showStatuses'     => true,
    'publishedAt'      => null,
    'publishedAtName'  => 'published_at',
    'showPublishedAt'  => true,
    'showFeatured'     => false,
    'featured'         => false,
    'featuredLabel'    => null,
    'indexRoute'       => null,
    'backLabel'        => null,
    'saveLabel'        => null,
    'saveAndEditLabel' => null,
    'previewUrl'       => null,
    'previewLabel'     => null,
    'deleteRoute'      => null,
    'deleteLabel'      => null,
    'defaultNow'       => false,
])

@php
    $title            = $title ?: __('Xuất bản');
    $statusLabel      = $statusLabel ?: __('Trạng thái');
    $featuredLabel    = $featuredLabel ?: __('Đánh dấu nổi bật');
    $backLabel        = $backLabel ?: __('Quay lại');
    $saveLabel        = $saveLabel ?: __('Lưu');
    $saveAndEditLabel = $saveAndEditLabel ?: __('Lưu & Sửa');
    $previewLabel     = $previewLabel ?: __('Xem trước');
    $deleteLabel      = $deleteLabel ?: __('Xóa');
    $statusVal        = $status instanceof \BackedEnum ? $status->value : (string) ($status !== null ? $status : 'draft');
    $current          = old($statusName, $statusVal);
@endphp

<x-admin.card :title="$title">
    @if($showStatuses && !empty($statuses))
        <x-admin.form-group :label="$statusLabel" :name="$statusName">
            <x-admin.select :name="$statusName">
                @foreach($statuses as $val => $lbl)
                    <option value="{{ $val }}" @selected((string)$current === (string)$val)>{{ $lbl }}</option>
                @endforeach
            </x-admin.select>
        </x-admin.form-group>
    @endif

    @if($showPublishedAt)
        <x-admin.datetime-field
            :name="$publishedAtName"
            :label="__('Ngày xuất bản')"
            :value="$publishedAt"
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
            <x-admin.button href="{{ $indexRoute }}" variant="outline-secondary">
                {{ $backLabel }}
            </x-admin.button>
        @endif

        @if($previewUrl)
            <x-admin.button href="{{ $previewUrl }}" variant="outline-secondary" target="_blank" rel="noopener">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                {{ $previewLabel }}
            </x-admin.button>
        @endif

        <x-admin.button type="submit" name="submit_action" value="save" variant="primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ $saveLabel }}
        </x-admin.button>
        <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="secondary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            {{ $saveAndEditLabel }}
        </x-admin.button>

        @if($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" class="ms-auto form-confirm"
                  data-confirm-text="{{ __('Bạn có chắc chắn muốn xóa? Thao tác không thể hoàn tác.') }}"
                  data-confirm-title="{{ __('Xóa mục này?') }}"
                  data-confirm-btn="{{ $deleteLabel }}">
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ $deleteLabel }}
                </x-admin.button>
            </form>
        @endif
    </div>
</x-admin.card>
