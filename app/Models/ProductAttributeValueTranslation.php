<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValueTranslation extends Model
{
    use HasFactory;

    protected $table = 'product_attribute_value_translations';

    protected $fillable = [
        'attribute_value_id',
        'locale',
        'value',
    ];

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class, 'attribute_value_id');
    }
}
