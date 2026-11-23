<?php

namespace App\Models;

use App\Core\Enums\ContentStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentBlock extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'status',
        'published_at',
        'image',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ContentBlockTranslation::class);
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image');
    }

    public function translate(string $locale): ?ContentBlockTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function translationFor(string $locale, ?string $fallbackLocale = null): ?ContentBlockTranslation
    {
        return $this->translate($locale)
            ?? ($fallbackLocale ? $this->translate($fallbackLocale) : null)
            ?? $this->translations->first();
    }

    public function getTitleAttribute(): string
    {
        return $this->translationFor(app()->getLocale())?->title ?? '---';
    }

    public function getSlugAttribute(): string
    {
        return $this->translationFor(app()->getLocale())?->slug ?? '';
    }

    public function getShortcodeAttribute(): string
    {
        return '[content_block id="'.$this->uuid.'"]';
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imageMedia?->getThumbUrl();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '---';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'secondary';
    }
}
