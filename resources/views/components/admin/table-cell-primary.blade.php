@props([
    'image'      => null,  // URL ảnh thumbnail
    'title'      => '',    // Tiêu đề chính (dòng 1, đậm)
    'subtitle'   => null,  // Phụ đề (dòng 2, mờ) — thường là slug
    'actions'    => [],    // Mảng actions cho x-admin.row-actions
    'initial'    => null,  // Ký tự đầu tiên để hiển thị thay thế ảnh (gradient)
    'imageShape' => 'rounded', // 'rounded' | 'square' | 'circle'
])

@php
    $shapeClass = match($imageShape) {
        'square' => 'rounded-0',
        'circle' => 'rounded-circle',
        default  => 'rounded',
    };
    $firstChar = $initial ?: mb_substr((string) $title, 0, 1);
@endphp

<div class="d-flex align-items-center gap-3">
    {{-- Thumbnail --}}
    @if($image)
        <img src="{{ $image }}" alt="" class="{{ $shapeClass }} object-fit-cover border flex-shrink-0" style="width:3rem;height:2.25rem;">
    @elseif($firstChar)
        <div class="{{ $shapeClass }} d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0 border" style="width:3rem;height:2.25rem;background:linear-gradient(135deg,#2563eb,#06b6d4);">
            {{ mb_strtoupper($firstChar) }}
        </div>
    @else
        <div class="{{ $shapeClass }} d-flex align-items-center justify-content-center bg-body-tertiary border text-body-secondary flex-shrink-0" style="width:3rem;height:2.25rem;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    @endif

    {{-- Text + Actions --}}
    <div class="min-w-0">
        <p class="fw-semibold small mb-0 text-truncate" style="max-width:20rem;">{{ $title }}</p>
        @if($subtitle)
            <p class="small text-body-secondary mb-0 text-truncate" style="max-width:20rem;">{{ $subtitle }}</p>
        @endif
        @if(!empty($actions))
            <x-admin.row-actions :actions="$actions" />
        @endif
    </div>
</div>
