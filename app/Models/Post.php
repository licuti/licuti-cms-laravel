<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUuid, SoftDeletes, HasSeo;

    protected $fillable = [
        'uuid', 'status', 'is_featured', 'view_count',
        'author_id', 'image', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
        'view_count'   => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'post_category_post');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('name')->join(', ') ?: '---';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('imageMedia') && $this->imageMedia) {
            return $this->imageMedia->getUrl();
        }
        if (!empty($this->image)) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->image)) {
                $media = Media::where('uuid', $this->image)->first();
                if ($media) {
                    return $media->getUrl();
                }
            } else {
                return str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image);
            }
        }
        return null;
    }

    /** Lấy bản dịch theo locale */
    public function translate(string $locale): ?PostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /** Tiêu đề theo ngôn ngữ hiện tại (fallback bản dịch đầu tiên) */
    public function getTitleAttribute(): string
    {
        return $this->translate(app()->getLocale())?->title
            ?? $this->translations->first()?->title
            ?? '---';
    }

    /** Slug theo ngôn ngữ hiện tại */
    public function getSlugAttribute(): string
    {
        return $this->translate(app()->getLocale())?->slug
            ?? $this->translations->first()?->slug
            ?? '';
    }

    /** Nhãn trạng thái hiển thị */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Đã xuất bản',
            'archived'  => 'Lưu trữ',
            default     => 'Bản nháp',
        };
    }

    /** Màu badge tương ứng trạng thái */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'archived'  => 'secondary',
            default     => 'warning',
        };
    }
}
