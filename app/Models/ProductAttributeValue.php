<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductAttributeValue extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'product_attribute_values';

    protected $fillable = [
        'uuid',
        'attribute_id',
        'value',
        'color_code',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_attribute_value',
            'attribute_value_id',
            'product_id'
        )->withTimestamps();
    }
}
