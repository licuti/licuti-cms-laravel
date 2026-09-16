<?php

namespace App\Models;

use App\Core\Enums\ContentStatus;
use App\Core\Enums\PageTemplate;
use App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasUuid, SoftDeletes, HasSeo;

    protected $fillable = [
        'uuid',
        'parent_id',
        'page_template',
        'image',
        'display_order',
        'status',
        'published_at',
    ];

    protected $casts = [
        'display_order'   => 'integer',
        'status'          => ContentStatus::class,
        'page_template'   => PageTemplate::class,
        'published_at'    => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translate(string $locale): ?PageTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function getTitleAttribute(): string
    {
        return $this->translate(app()->getLocale())?->title
            ?? $this->translations->first()?->title
            ?? '---';
    }

    public function getSlugAttribute(): string
    {
        return $this->translate(app()->getLocale())?->slug
            ?? $this->translations->first()?->slug
            ?? '';
    }

    public function getExcerptAttribute(): string
    {
        return $this->translate(app()->getLocale())?->excerpt
            ?? $this->translations->first()?->excerpt
            ?? '';
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('imageMedia') && $this->imageMedia) {
            return $this->imageMedia->getUrl();
        }

        if (!empty($this->image)) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->image)) {
                $media = Media::query()->where('uuid', $this->image)->first();
                if ($media) {
                    return $media->getUrl();
                }
            } elseif (str_starts_with($this->image, 'http')) {
                return $this->image;
            } else {
                return asset('storage/' . $this->image);
            }
        }

        return null;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', ContentStatus::ARCHIVED);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // ─── Accessor ──────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return ($this->status instanceof ContentStatus ? $this->status : ContentStatus::from((string) $this->status))->label();
    }

    public function getStatusColorAttribute(): string
    {
        return ($this->status instanceof ContentStatus ? $this->status : ContentStatus::from((string) $this->status))->color();
    }

    public function getTemplateLabelAttribute(): string
    {
        return ($this->page_template instanceof PageTemplate ? $this->page_template : PageTemplate::from((string) $this->page_template))->label();
    }

    public function getTemplateOptions(): array
    {
        return collect(PageTemplate::cases())
            ->mapWithKeys(fn(PageTemplate $t) => [$t->value => $t->label()])
            ->toArray();
    }
}