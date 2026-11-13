<?php

namespace App\Models\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ảnh chính của model, lưu trong cột `image` dưới dạng Media UUID, đường dẫn storage hoặc URL.
 *
 * Ảnh dạng đường dẫn cũ dùng disk trong `config('media.legacy_disk')`, không hardcode public/storage.
 * Eager-load `imageMedia` ở các query danh sách để tránh N+1 khi ảnh là UUID.
 */
trait HasImage
{
    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    public function imageUrl(string $variant = 'full'): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (Str::isUuid($this->image)) {
            $media = $this->imageMedia;

            return match (true) {
                ! $media => null,
                $variant === 'thumb' => $media->getThumbUrl(),
                default => $media->getUrl(),
            };
        }

        return Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : Storage::disk(config('media.legacy_disk', 'public'))->url(ltrim($this->image, '/'));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl();
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imageUrl('thumb');
    }
}
