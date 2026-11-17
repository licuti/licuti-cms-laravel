@props([
    'name',
    'current'     => null,
    'currentUuid' => null,
    'shape'       => 'square',
    'label'       => null,
    'description' => null,
    // uuid: gửi cặp {name}_uuid + {name}_remove (ảnh đại diện)
    // url:  gửi thẳng URL trong input {name} (og_image, ảnh ngoài)
    'return'      => 'uuid',
    'inputClass'  => null,
])

@php
    $nameSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $name);
    $id = 'img_field_' . $nameSlug . '_' . uniqid();
    $isUrl = $return === 'url';
    $containerClass = match($shape) {
        'circle' => 'rounded-circle aspect-square',
        'wide'   => 'rounded-3 aspect-video',
        default  => 'rounded-3 aspect-square',
    };
    $hasImage = !empty($current);
@endphp

<div {{ $attributes->merge(['class' => 'image-upload-wrapper']) }}
    id="{{ $id }}" data-field-name="{{ $name }}" data-picker-target="{{ $id }}" data-return="{{ $return }}">
    @if ($label) <p class="fw-semibold small mb-2">{{ $label }}</p> @endif

    @if ($isUrl)
        <div class="input-group">
            <span class="input-group-text p-1 bg-body-tertiary">
                <span class="d-inline-flex align-items-center justify-content-center rounded border overflow-hidden bg-body-tertiary" style="width: 2.25rem; height: 2.25rem;">
                    <svg class="thumb-placeholder text-body-tertiary {{ $hasImage ? 'd-none' : '' }}" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <img class="preview-image w-100 h-100 object-fit-cover {{ $hasImage ? '' : 'd-none' }}" @if ($hasImage) src="{{ $current }}" @endif alt="" draggable="false">
                </span>
            </span>
            <input
                type="text"
                name="{{ $name }}"
                value="{{ $current }}"
                class="form-control {{ $inputClass }}"
                placeholder="https://..."
            >
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 js-open-picker">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ __('Chọn ảnh') }}</span>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-image-clear title="{{ __('Bỏ chọn ảnh') }}">&times;</button>
        </div>
    @else
        <div class="position-relative">
            <div class="preview-container d-flex align-items-center justify-content-center border border-1 border-dashed border-secondary-subtle bg-body-tertiary overflow-hidden {{ $containerClass }}">
                <div class="placeholder bg-transparent d-flex flex-column align-items-center justify-content-center gap-2 p-3 text-center {{ $hasImage ? 'd-none' : '' }}">
                    <svg width="36" height="36" class="text-body-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="placeholder-text small text-body-secondary">Chưa có ảnh</span>
                </div>
                @if ($hasImage)
                    <img src="{{ $current }}" class="preview-image w-100 h-100 object-fit-cover" alt="Preview" draggable="false">
                @else
                    <img class="preview-image w-100 h-100 object-fit-cover d-none" alt="Preview" draggable="false">
                @endif
            </div>
            <button type="button" class="btn-clear-image position-absolute top-0 end-0 m-2 {{ $hasImage ? '' : 'd-none' }}" data-image-clear title="Bỏ chọn ảnh">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <input type="hidden" name="{{ $name }}_uuid" class="input-media-uuid" value="{{ $currentUuid ?? '' }}">
        <input type="hidden" name="{{ $name }}_remove" class="input-remove-flag" value="0">
    @endif

    @if ($description) <p class="form-text mt-1 mb-0">{{ $description }}</p> @endif
</div>
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function openPickerFor(wrapper) {
        var modal = document.getElementById('global-media-picker');
        if (!modal) return;
        modal.dataset.activeTarget = wrapper.id;
        delete modal.dataset.multi; // image-upload đơn luôn ở single-select mode
        var uuidInput = wrapper.querySelector('.input-media-uuid');
        if (uuidInput && uuidInput.value) modal.dataset.currentId = uuidInput.value;
        openModal('global-media-picker');
        document.dispatchEvent(new CustomEvent('global-media-picker-open'));
    }

    // Mở picker: click vùng preview (chế độ uuid) hoặc nút "Chọn ảnh" (chế độ url)
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('[data-image-clear]')) return;
        var wrapper = e.target.closest('.image-upload-wrapper');
        if (!wrapper) return;
        if (!e.target.closest('.js-open-picker') && !e.target.closest('.preview-container')) return;
        openPickerFor(wrapper);
    });

    document.body.addEventListener('media-picker-selected', function(e) {
        var wrapper = e.target.closest('.image-upload-wrapper');
        if (!wrapper) return;
        var item = e.detail;
        if (!item) return;
        var url = item.url || item.original_url || '';
        var previewImg = wrapper.querySelector('.preview-image');
        var placeholder = wrapper.querySelector('.placeholder');
        var thumbPlaceholder = wrapper.querySelector('.thumb-placeholder');
        var clearBtn = wrapper.querySelector('[data-image-clear]');

        if (wrapper.dataset.return === 'url') {
            var urlInput = wrapper.querySelector('input[type="text"][name]');
            if (urlInput) {
                urlInput.value = url;
                // Cho các preview khác (vd. Facebook) cập nhật ngay
                urlInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } else {
            var uuidInput = wrapper.querySelector('.input-media-uuid');
            var removeInput = wrapper.querySelector('.input-remove-flag');
            if (uuidInput) uuidInput.value = item.uuid;
            if (removeInput) removeInput.value = '0';
        }

        if (previewImg) { previewImg.src = url; previewImg.classList.remove('d-none'); }
        if (placeholder) placeholder.classList.add('d-none');
        if (thumbPlaceholder) thumbPlaceholder.classList.add('d-none');
        if (clearBtn) clearBtn.classList.remove('d-none');
    });

    document.body.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-image-clear]');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        var wrapper = btn.closest('.image-upload-wrapper');
        if (!wrapper) return;
        var previewImg = wrapper.querySelector('.preview-image');

        if (wrapper.dataset.return === 'url') {
            var urlInput = wrapper.querySelector('input[type="text"][name]');
            if (urlInput) {
                urlInput.value = '';
                urlInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (previewImg) { previewImg.src = ''; previewImg.classList.add('d-none'); }
            var thumbPlaceholder = wrapper.querySelector('.thumb-placeholder');
            if (thumbPlaceholder) thumbPlaceholder.classList.remove('d-none');
            return;
        }

        var placeholder = wrapper.querySelector('.placeholder');
        var uuidInput = wrapper.querySelector('.input-media-uuid');
        var removeInput = wrapper.querySelector('.input-remove-flag');
        if (uuidInput) uuidInput.value = '';
        if (removeInput) removeInput.value = '1';
        if (previewImg) { previewImg.src = ''; previewImg.classList.add('d-none'); }
        if (placeholder) placeholder.classList.remove('d-none');
        btn.classList.add('d-none');
    });
});
</script>
@endpush
@endonce
