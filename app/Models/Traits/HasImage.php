<?php

namespace App\Models\Traits;

use App\Models\Media;
use App\Services\Shared\Media\MediaUrlResolver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Ảnh chính của model, lưu trong cột `image` dưới dạng Media UUID, đường dẫn storage hoặc URL.
 *
 * Việc "giá trị cột → URL" do MediaUrlResolver đảm nhiệm (không phụ thuộc tên cột),
 * nên thêm cột ảnh mới chỉ cần gọi mediaUrl('banner', 'thumb') thay vì viết accessor mới.
 *
 * Eager-load `imageMedia` ở các query danh sách để tránh N+1 khi ảnh là UUID.
 */
trait HasImage
{
    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    /**
     * URL ảnh của một cột bất kỳ trên model (image, banner, ...).
     */
    public function mediaUrl(string $column, string $variant = 'full'): ?string
    {
        $resolver = app(MediaUrlResolver::class);

        // Quan hệ {column}Media đã eager-load (vd: with('imageMedia')) -> đưa vào resolver,
        // tránh resolver query lại lần nữa.
        $relation = Str::camel($column) . 'Media';
        if (method_exists($this, $relation) && $this->relationLoaded($relation)) {
            $resolver->register($this->{$relation});
        }

        $value = $this->{$column};

        return $resolver->url($value !== null ? (string) $value : null, $variant);
    }

    public function imageUrl(string $variant = 'full'): ?string
    {
        return $this->mediaUrl('image', $variant);
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
