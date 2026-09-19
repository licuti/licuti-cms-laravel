<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'product_attributes';

    protected $fillable = [
        'uuid',
        'code',
        'type',
        'is_filterable',
        'display_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'display_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeTranslation::class, 'attribute_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')->orderBy('display_order');
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