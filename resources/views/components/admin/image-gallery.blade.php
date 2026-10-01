@props([
    'name'        => 'images',
    'images'      => null,
    'shape'       => 'square',
    'description' => null,
    'max'         => 10,
    'itemWidth'   => 120,
])

@php
    $galleryId = 'gallery_' . str_replace(['[', ']'], '_', (string) $name) . '_' . uniqid();

    // Khi validation fail, phục há»“i gallery từ old() thay vì DB
    $hasErrors = session()->has('errors');
    $oldImages = old('images');

    $items = [];

    if ($hasErrors && is_array($oldImages)) {
        foreach ($oldImages as $i => $img) {
            if (!empty($img['image_remove']) || empty($img['image_uuid'])) {
                continue;
            }

            $uuid = $img['image_uuid'];
            $url  = null;

            if (str_starts_with($uuid, 'http://') || str_starts_with($uuid, 'https://')) {
                $url = $uuid;
            } else {
                $media = \App\Models\Media::where('uuid', $uuid)->first();
                $url   = $media?->getUrl() ?? ($media?->file_path ? asset('storage/' . $media->file_path) : null);
            }

            $items[] = [
                'index' => $i,
                'uuid'  => $uuid,
                'url'   => $url,
            ];
        }
    } else {
        foreach (collect($images ?? [])->values() as $i => $img) {
            $items[] = [
                'index' => $i,
                'uuid'  => $img->image ?? ($img['image'] ?? null),
                'url'   => $img->url ?? ($img['url'] ?? null),
            ];
        }
    }

    $nextIndex = $items ? max(array_column($items, 'index')) + 1 : 0;
@endphp

<div {{ $attributes->merge(['class' => 'image-gallery']) }}
    id="{{ $galleryId }}"
    data-next-index="{{ $nextIndex }}"
    data-max="{{ $max }}"
    style="--gallery-item-size: {{ $itemWidth }}px;">

    <div class="gallery-items">
        @foreach ($items as $item)
            <div class="gallery-item" draggable="true" data-index="{{ $item['index'] }}">
                <x-admin.image-upload
                    name="{{ $name }}[{{ $item['index'] }}][image]"
                    :current="$item['url']"
                    :current-uuid="$item['uuid']"
                    :shape="$shape"
                />
            </div>
        @endforeach

        <button type="button" class="gallery-add-tile btn-add-gallery-image" title="{{ __('Thêm ảnh') }}">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span class="small">{{ __('Thêm ảnh') }}</span>
        </button>
    </div>

    @if ($description)
        <p class="form-text mt-2">{{ $description }}</p>
    @endif

    {{-- Template item dùng Ä‘á»ƒ clone khi click "Thêm ảnh" (ná»™i dung inert cho Ä‘ến khi clone) --}}
    <template id="{{ $galleryId }}_template">
        <div class="gallery-item" draggable="true" data-index="__INDEX__">
            <x-admin.image-upload
                name="{{ $name }}[__INDEX__][image]"
                :shape="$shape"
            />
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Má»Ÿ thư viá»‡n á»Ÿ chế Ä‘á»™ chọn nhiều khi bấm "Thêm ảnh"
    document.body.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.btn-add-gallery-image');
        if (!addBtn) return;

        var gallery = addBtn.closest('.image-gallery');
        if (!gallery) return;

        var max = parseInt(gallery.dataset.max || '10', 10);
        if (gallery.querySelectorAll('.gallery-item').length >= max) return;

        var modal = document.getElementById('global-media-picker');
        if (modal) {
            modal.dataset.activeTarget = gallery.id;
            modal.dataset.multi = '1';
            openModal('global-media-picker');
            document.dispatchEvent(new CustomEvent('global-media-picker-open'));
        }
    });

    // Nhận danh sách nhiều ảnh từ picker -> sinh các item Ä‘ã có sẵn ảnh
    document.body.addEventListener('media-picker-multi-selected', function (e) {
        var gallery = e.target.closest('.image-gallery');
        if (!gallery) return;

        var items = (e.detail && e.detail.items) || [];
        if (!items.length) return;

        var max = parseInt(gallery.dataset.max || '10', 10);
        var tpl = document.getElementById(gallery.id + '_template');
        if (!tpl) return;

        var itemsHolder = gallery.querySelector('.gallery-items');
        var addBtn = gallery.querySelector('.btn-add-gallery-image');

        items.forEach(function (item) {
            if (gallery.querySelectorAll('.gallery-item').length >= max) return;

            var idx = parseInt(gallery.dataset.nextIndex || '0', 10);
            gallery.dataset.nextIndex = String(idx + 1);

            // Thay index + clone markup
            var html = tpl.innerHTML.split('__INDEX__').join(String(idx));
            var holder = document.createElement('div');
            holder.innerHTML = html.trim();

            var itemEl = holder.firstElementChild;
            if (!itemEl) return;

            // Điền sẵn uuid + preview ảnh (giá»‘ng flow chọn 1 ảnh)
            var uuidInput = itemEl.querySelector('.input-media-uuid');
            var previewImg = itemEl.querySelector('.preview-image');
            var placeholder = itemEl.querySelector('.placeholder');
            var clearBtn = itemEl.querySelector('.btn-clear-image');
            var removeInput = itemEl.querySelector('.input-remove-flag');

            if (uuidInput) uuidInput.value = item.uuid || '';
            if (previewImg) {
                previewImg.src = item.url || item.original_url || '';
                previewImg.classList.remove('d-none');
            }
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
            if (removeInput) removeInput.value = '0';

            itemsHolder.insertBefore(itemEl, addBtn);
        });
    });

    // Gỡ ảnh khỏi album: nút X trên ảnh (btn-clear-image) gỡ hẳn item khỏi DOM.
    // image-upload Ä‘ơn ngoài gallery giữ behaviour cũ (chá»‰ xoá giá trá»‹, không xoá ô).
    document.body.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-clear-image');
        if (!btn) return;
        var gallery = btn.closest('.image-gallery');
        if (!gallery) return;
        var item = btn.closest('.gallery-item');
        if (item) item.remove();
    });

    // Kéo thả Ä‘á»ƒ sắp xếp (HTML5 drag & drop; mobile dùng nút mũi tên)
    var dragSrc = null;
    document.body.addEventListener('dragstart', function (e) {
        var item = e.target.closest('.gallery-item');
        if (!item) return;
        dragSrc = item;
        item.classList.add('gallery-item-dragging');
        try { e.dataTransfer.setData('text/plain', item.dataset.index || ''); } catch (err) {}
        e.dataTransfer.effectAllowed = 'move';
    });
    document.body.addEventListener('dragover', function (e) {
        if (!dragSrc) return;
        var item = e.target.closest('.gallery-item');
        if (!item || item === dragSrc) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        item.classList.add('gallery-item-drop-target');
    });
    document.body.addEventListener('dragleave', function (e) {
        var item = e.target.closest('.gallery-item');
        if (item) item.classList.remove('gallery-item-drop-target');
    });
    document.body.addEventListener('drop', function (e) {
        if (!dragSrc) return;
        var item = e.target.closest('.gallery-item');
        e.preventDefault();
        if (item && item !== dragSrc) {
            // Ghi lại vị trí cũ của các item trước khi di chuyển (FLIP animation)
            var galleryEl = dragSrc.closest('.image-gallery');
            var flipItems = galleryEl ? Array.prototype.slice.call(galleryEl.querySelectorAll('.gallery-item')) : [];
            var firstPos = new Map();
            flipItems.forEach(function (el) { firstPos.set(el, el.getBoundingClientRect()); });

            // Thả vào nửa trên -> chèn trước, nửa dưới -> chèn sau
            var rect = item.getBoundingClientRect();
            var after = (e.clientY - rect.top) > rect.height / 2;
            item.parentNode.insertBefore(dragSrc, after ? item.nextSibling : item);

            // FLIP: Invert -> Play để các ảnh trượt mượt tới vị trí mới
            firstPos.forEach(function (first, el) {
                var last = el.getBoundingClientRect();
                var dx = first.left - last.left;
                var dy = first.top - last.top;
                if (dx === 0 && dy === 0) return;

                el.style.transition = 'none';
                el.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
                void el.offsetWidth; // ép reflow trước khi transitions
                el.style.transition = 'transform .28s cubic-bezier(.22,.61,.36,1)';
                el.style.transform = '';

                var done = function () {
                    el.style.transition = '';
                    el.style.transform = '';
                    el.removeEventListener('transitionend', done);
                };
                el.addEventListener('transitionend', done);
                setTimeout(done, 340); // fallback nếu transitionend không fire
            });
        }
        item && item.classList.remove('gallery-item-drop-target');
    });
    document.body.addEventListener('dragend', function () {
        if (dragSrc) dragSrc.classList.remove('gallery-item-dragging');
        document.querySelectorAll('.gallery-item-drop-target').forEach(function (el) {
            el.classList.remove('gallery-item-drop-target');
        });
        dragSrc = null;
    });
});
</script>
@endpush
@endonce
