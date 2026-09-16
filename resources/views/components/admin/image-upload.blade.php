@props([
    'name',
    'current'     => null,
    'currentUuid' => null,
    'shape'       => 'square',
    'label'       => null,
    'description' => null,
])

@php
    $id = 'img_field_' . $name . '_' . uniqid();
    $containerClass = match($shape) {
        'circle' => 'rounded-circle aspect-square',
        'wide'   => 'rounded-3 aspect-video',
        default  => 'rounded-3 aspect-square',
    };
    $hasImage = !empty($current);
@endphp

<div {{ $attributes->merge(['class' => 'image-upload-wrapper']) }}
    id="{{ $id }}" data-field-name="{{ $name }}" data-picker-target="{{ $id }}">
    @if ($label) <p class="fw-semibold small mb-2">{{ $label }}</p> @endif

    <div class="position-relative">
        <div class="preview-container d-flex align-items-center justify-content-center border border-2 border-dashed border-secondary-subtle bg-body-tertiary overflow-hidden {{ $containerClass }}">
            <div class="placeholder bg-transparent d-flex flex-column align-items-center justify-content-center gap-2 p-3 text-center {{ $hasImage ? 'd-none' : '' }}">
                <svg width="36" height="36" class="text-body-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="small text-body-secondary" style="font-size:0.6875rem;">Chưa có ảnh</span>
            </div>
            @if ($hasImage)
                <img src="{{ $current }}" class="preview-image w-100 h-100 object-fit-cover" alt="Preview">
            @else
                <img class="preview-image w-100 h-100 object-fit-cover d-none" alt="Preview">
            @endif
        </div>
        <button type="button" class="btn-clear-image position-absolute top-0 end-0 m-2 btn btn-danger btn-sm rounded-circle shadow-sm {{ $hasImage ? '' : 'd-none' }}" title="Bỏ chọn ảnh" style="width:1.5rem;height:1.5rem;z-index:10;padding:0.15rem;">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <button type="button" class="btn-open-picker mt-2 w-100 d-flex align-items-center justify-content-center gap-2 px-3 py-2 btn btn-outline-secondary btn-sm fw-semibold" data-target="{{ $id }}">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>{{ $hasImage ? 'Thay đổi ảnh' : 'Chọn ảnh' }}</span>
    </button>

    @if ($description) <p class="form-text mt-1">{{ $description }}</p> @endif
    <input type="hidden" name="{{ $name }}_uuid" class="input-media-uuid" value="{{ $currentUuid ?? '' }}">
    <input type="hidden" name="{{ $name }}_remove" class="input-remove-flag" value="0">
</div>
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-open-picker');
        if (!btn) return;
        var wrapperId = btn.dataset.target;
        var wrapper = document.getElementById(wrapperId);
        var modal = document.getElementById('global-media-picker');
        if (modal) {
            modal.dataset.activeTarget = wrapperId;
            if (wrapper) {
                var uuidInput = wrapper.querySelector('.input-media-uuid');
                if (uuidInput && uuidInput.value) modal.dataset.currentId = uuidInput.value;
            }
            openModal('global-media-picker');
            document.dispatchEvent(new CustomEvent('global-media-picker-open'));
        }
    });
    document.body.addEventListener('media-picker-selected', function(e) {
        var wrapper = e.target.closest('.image-upload-wrapper');
        if (!wrapper) return;
        var item = e.detail;
        if (!item) return;
        var previewImg = wrapper.querySelector('.preview-image');
        var placeholder = wrapper.querySelector('.placeholder');
        var clearBtn = wrapper.querySelector('.btn-clear-image');
        var openBtn = wrapper.querySelector('.btn-open-picker');
        var uuidInput = wrapper.querySelector('.input-media-uuid');
        var removeInput = wrapper.querySelector('.input-remove-flag');
        previewImg.src = item.url || item.original_url || '';
        previewImg.classList.remove('d-none');
        placeholder.classList.add('d-none');
        clearBtn.classList.remove('d-none');
        openBtn.querySelector('span').textContent = 'Thay đổi ảnh';
        uuidInput.value = item.uuid;
        removeInput.value = '0';
    });
    document.body.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-clear-image');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        var wrapper = btn.closest('.image-upload-wrapper');
        if (!wrapper) return;
        var previewImg = wrapper.querySelector('.preview-image');
        var placeholder = wrapper.querySelector('.placeholder');
        var openBtn = wrapper.querySelector('.btn-open-picker');
        var uuidInput = wrapper.querySelector('.input-media-uuid');
        var removeInput = wrapper.querySelector('.input-remove-flag');
        previewImg.src = '';
        previewImg.classList.add('d-none');
        placeholder.classList.remove('d-none');
        btn.classList.add('d-none');
        openBtn.querySelector('span').textContent = 'Chọn ảnh';
        uuidInput.value = '';
        removeInput.value = '1';
    });
});
</script>
@endpush
@endonce