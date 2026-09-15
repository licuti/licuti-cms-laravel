<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostCategory extends Model
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
        return $this->hasMany(PostCategoryTranslation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PostCategory::class, 'parent_id');
    }

    /**
     * Trả về toàn bộ danh mục dưới dạng cây phân cấp (Collection có thuộc tính _children).
     * Gọi 1 query duy nhất, ráp cây trong bộ nhớ — không có N+1.
     *
     * @return \Illuminate\Support\Collection<static>
     */
    public static function toTree(): \Illuminate\Support\Collection
    {
        $all = static::with('translations')->orderBy('display_order')->get();

        $buildBranch = function (mixed $parentId) use ($all, &$buildBranch) {
            return $all
                ->filter(fn ($item) => (string) $item->parent_id === (string) $parentId
                    || ($parentId === null && $item->parent_id === null))
                ->map(function ($item) use (&$buildBranch) {
                    $item->_children = $buildBranch($item->id);
                    return $item;
                })
                ->values();
        };

        return $buildBranch(null);
    }

    /**
     * Helper to get translation by locale
     */
    public function translate(string $locale): ?PostCategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * Accessors
     */
    public function getNameAttribute(): string
    {
        return $this->translate(app()->getLocale())?->name
            ?? $this->translations->first()?->name
            ?? '-';
    }

    public function getTranslatedNameAttribute(): string
    {
        return $this->getNameAttribute();
    }

    public function getTranslatedSlugAttribute(): string
    {
        return $this->translate(app()->getLocale())?->slug
            ?? $this->translations->first()?->slug
            ?? '';
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
            // Không query trực tiếp DB ở đây để tránh N+1. Nếu muốn hiển thị ảnh, phải eager load `imageMedia`.
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->image)) {
                return asset('storage/' . $this->image);
            }
        }
        
        return null;
    }
}