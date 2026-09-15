<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory, HasUuid, HasSeo;

    protected $fillable = [
        'uuid',
        'parent_id',
        'image',
        'icon',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relations
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Accessors
     */
    public function getTranslatedNameAttribute(): string
    {
        if (!$this->relationLoaded('translations') || $this->translations->isEmpty()) {
            return $this->name ?? '-';
        }

        $locale = app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->first();

        return $translation->name ?? '-';
    }

    public function getTranslatedSlugAttribute(): string
    {
        if (!$this->relationLoaded('translations') || $this->translations->isEmpty()) {
            return $this->slug ?? '';
        }

        $locale = app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->first();

        return $translation->slug ?? '';
    }

    public function getParentTranslatedNameAttribute(): string
    {
        if (!$this->relationLoaded('parent') || !$this->parent) {
            return '-';
        }

        return $this->parent->translated_name;
    }

    
    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Media::class, 'image', 'uuid');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('imageMedia') && $this->imageMedia) {
            return $this->imageMedia->getThumbUrl();
        }
        if (!empty($this->image)) {
            // Check if it's a UUID
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->image)) {
                $media = \App\Models\Media::where('uuid', $this->image)->first();
                if ($media) return $media->getThumbUrl();
            } else {
                return asset('storage/' . $this->image);
            }
        }
        return null;
    }

    // TODO (Future): Move presentation logic to a Presenter/View Data object to maintain SRP.
    public function getAvailableActionsAttribute(): array
    {
        $uuid = $this->uuid ?? $this->id;

        return [
            [
                'label' => __('Sửa'),
                'route' => route('admin.categories.edit', $uuid),
                'method' => 'GET',
                'color' => 'blue',
            ],
            [
                'label'         => __('Xóa'),
                'route'         => route('admin.categories.destroy', $uuid),
                'method'        => 'DELETE',
                'color'         => 'red',
                'confirm_title' => __('Xóa danh mục?'),
                'confirm_text'  => __('Thao tác này không thể hoàn tác.'),
                'confirm_btn'   => __('Xóa ngay')
            ],
        ];
    }
}