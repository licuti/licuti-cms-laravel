<?php

namespace App\Models;

use App\Core\Enums\ContentStatus;
use App\Models\Traits\HasSeo;
use App\Models\Traits\HasUuid;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasSeo, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'status', 'is_featured', 'view_count',
        'author_id', 'image', 'published_at',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->imageMedia?->getUrl();
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imageMedia?->getThumbUrl();
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

    /** Lấy bản dịch theo locale */
    public function translate(string $locale): ?PostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function translationFor(string $locale, ?string $fallbackLocale = null): ?PostTranslation
    {
        return $this->translate($locale)
            ?? ($fallbackLocale ? $this->translate($fallbackLocale) : null)
            ?? $this->translations->first();
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
        return $this->status?->label() ?? '---';
    }

    /** Màu badge tương ứng trạng thái */
    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'secondary';
    }
}
