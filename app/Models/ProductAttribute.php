<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'product_attributes';

    protected $fillable = [
        'uuid',
        'product_id',
        'code',
        'type',
        'is_filterable',
        'display_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'display_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeTranslation::class, 'attribute_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')->orderBy('display_order');
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('product_id');
    }

    public function isGlobal(): bool
    {
        return is_null($this->product_id);
    }

    public function translate(?string $locale = null): ?ProductAttributeTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'vi'))
            ?? $this->translations->first();
    }

    public function getNameAttribute(): string
    {
        return $this->translate()?->name ?? $this->code ?? '';
    }
}