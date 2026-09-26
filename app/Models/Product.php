<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasSeo, HasUuid, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'category_id',
        'brand_id',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_inventory',
        'weight',
        'dimensions',
        'primary_image',
        'is_featured',
        'is_active',
        'status',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_inventory' => 'boolean',
        'weight' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class, 'product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('display_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id')->where('is_primary', true);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'product_attribute',
            'product_id',
            'attribute_id'
        )
            ->withPivot(['is_variation', 'display_order'])
            ->withTimestamps()
            ->orderBy('pivot_display_order', 'asc');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_attribute_value',
            'product_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->orderBy('display_order');
    }

    public function translate(?string $locale = null): ?ProductTranslation
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'vi'))
            ?? $this->translations->first();
    }

    public function getNameAttribute(): string
    {
        return $this->translate()?->name ?? '';
    }

    public function getSlugAttribute(): string
    {
        return $this->translate()?->slug ?? '';
    }

    public function primaryImageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'primary_image', 'uuid');
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        // Ưu tiên cột primary_image (P0.2), fallback về ảnh đầu tiên của gallery
        if (!empty($this->primary_image)) {
            $image = $this->primary_image;

            if (preg_match('/^[0-9a-f-]{36}$/i', $image)) {
                $media = Media::where('uuid', $image)->first();

                return $media?->getUrl();
            }

            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            return asset('storage/'.$image);
        }

        // Backward-compat: dữ liệu cũ chỉ có is_primary trên product_images
        $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $primary?->url;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
