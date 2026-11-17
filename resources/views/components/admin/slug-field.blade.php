@props([
    'label' => null,
    'name' => null,                 // tên input (dạng mảng): translations[vi][slug]
    'errorName' => null,            // tên dùng để hiển thị lỗi (dạng chấm): translations.vi.slug
    'value' => '',
    'baseUrl' => null,              // ví dụ: url('/posts')
    'placeholder' => 'vi-du-lien-ket',
    'displayPlaceholder' => null,   // chữ hiển thị khi chưa có slug
    'description' => null,
    'maxlength' => 255,
    'viewUrl' => null,
])

@php
    $errorKey = $errorName ?? $name;
    $slugValue = (string) old($errorKey, $value);
    $inputId = 'slug-' . substr(md5((string) $name), 0, 8);
    $displayPlaceholder = $displayPlaceholder ?? __('(chưa có liên kết)');
    $base = rtrim((string) $baseUrl, '/');
@endphp

{{-- Permalink kiểu WordPress: "Đường dẫn: <url>/<slug>". Chỉ có MỘT thẻ slug (input),
     trạng thái hiển thị/sửa do class .is-editing trên hàng quyết định. --}}
<div class="slug-field" data-slug-field>
    <x-admin.form-group :label="$label" :name="$errorKey" :description="$description" :for="$inputId">
        <div class="slug-permalink">
            <span class="slug-permalink-label">{{ __('Đường dẫn:') }}</span>
            <span class="slug-permalink-base">{{ $base }}/</span>
            <input
                type="text"
                id="{{ $inputId }}"
                name="{{ $name }}"
                value="{{ $slugValue }}"
                class="slug-permalink-slug seo-source-slug {{ $errors->has($errorKey) ? 'is-invalid' : '' }}"
                placeholder="{{ $displayPlaceholder }}"
                maxlength="{{ $maxlength }}"
                autocomplete="off"
                readonly
                data-slug-input
            >
            <button type="button" class="btn btn-link slug-permalink-edit" data-slug-edit>{{ __('Chỉnh sửa') }}</button>
            <button type="button" class="btn btn-link slug-edit-ok d-none" data-slug-ok>{{ __('Xác nhận') }}</button>
            @if($viewUrl)
                <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="slug-permalink-view">{{ __('Xem') }}</a>
            @endif
        </div>
    </x-admin.form-group>
</div>
