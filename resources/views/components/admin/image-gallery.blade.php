@props([
    'name'        => 'images',
    'images'      => null,
    'shape'       => 'square',
    'description' => null,
    'max'         => 10,
    'itemWidth'   => 150,
    'showPrimary' => true,
])

@php
    $galleryId = 'gallery_' . str_replace(['[', ']'], '_', (string) $name) . '_' . uniqid();

    $containerClass = match ($shape) {
        'circle' => 'rounded-circle aspect-square',
        'wide'   => 'rounded-3 aspect-video',
        default  => 'rounded-3 aspect-square',
    };

    // Khi validation fail, phục hồi gallery từ old() thay vì DB
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
    data-max="{{ $max }}">

    <div class="d-flex flex-wrap gap-3 gallery-items">
        @foreach ($items as $item)
            <div class="gallery-item d-flex flex-column" style="width: {{ $itemWidth }}px;">
                <x-admin.image-upload
                    name="{{ $name }}[{{ $item['index'] }}][image]"
                    :current="$item['url']"
                    :current-uuid="$item['uuid']"
                    :shape="$shape"
                />

                @if ($showPrimary)
                <div class="form-check mt-2">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="primary_index"
                        value="{{ $item['index'] }}"
                        id="primary_{{ $galleryId }}_{{ $item['index'] }}"
                    >
                    <label class="form-check-label small" for="primary_{{ $galleryId }}_{{ $item['index'] }}">
                        {{ __('Ảnh đại diện') }}
                    </label>
                </div>
                @endif
            </div>
        @endforeach
    </div>

    <button
        type="button"
        class="btn btn-outline-secondary btn-sm btn-add-gallery-image mt-1 d-inline-flex align-items-center gap-2"
    >
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>{{ __('Thêm ảnh') }}</span>
    </button>

    @if ($description)
        <p class="form-text mt-1">{{ $description }}</p>
    @endif

    {{-- Template item dùng để clone khi click "Thêm ảnh" (nội dung inert cho đến khi clone) --}}
    <template id="{{ $galleryId }}_template">
        <div class="gallery-item d-flex flex-column" style="width: {{ $itemWidth }}px;">
            <x-admin.image-upload
                name="{{ $name }}[__INDEX__][image]"
                :shape="$shape"
            />

            @if ($showPrimary)
            <div class="form-check mt-2">
                <input
                    class="form-check-input"
                    type="radio"
                    name="primary_index"
                    value="__INDEX__"
                    id="primary_{{ $galleryId }}___INDEX__"
                >
                <label class="form-check-label small" for="primary_{{ $galleryId }}___INDEX__">
                    {{ __('Ảnh đại diện') }}
                </label>
            </div>
            @endif
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.btn-add-gallery-image');
        if (!addBtn) return;

        var gallery = addBtn.closest('.image-gallery');
        if (!gallery) return;

        var max = parseInt(gallery.dataset.max || '10', 10);
        if (gallery.querySelectorAll('.gallery-item').length >= max) return;

        var tpl = document.getElementById(gallery.id + '_template');
        if (!tpl) return;

        var idx = parseInt(gallery.dataset.nextIndex || '0', 10);
        gallery.dataset.nextIndex = String(idx + 1);

        // Thay index + clone markup (id phát sinh từ name chứa __INDEX__ nên cũng unique theo idx)
        var html = tpl.innerHTML.split('__INDEX__').join(String(idx));
        var holder = document.createElement('div');
        holder.innerHTML = html.trim();

        var item = holder.firstElementChild;
        if (item) {
            gallery.querySelector('.gallery-items').appendChild(item);
        }
    });
});
</script>
@endpush
@endonce
