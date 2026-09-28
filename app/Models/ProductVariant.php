<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'product_variants';

    protected $fillable = [
        'uuid',
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'image',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'compare_price'  => 'decimal:2',
        'cost_price'     => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active'      => 'boolean',
        'display_order'  => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_attribute_values',
            'variant_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    /**
     * Tên biến thể = join tên các giá trị theo thứ tự thuộc tính, vd "Đỏ - S".
     */
    public function getNameAttribute(): string
    {
        return $this->attributeValues
            ->sortBy(fn ($value) => sprintf('%05d.%05d', $value->attribute?->display_order ?? 0, $value->display_order))
            ->map(fn ($value) => $value->value)
            ->implode(' - ');
    }

    /**
     * Combo key của biến thể: danh sách attribute_value id đã sắp xếp, join bằng '-'
     */
    public function getComboKeyAttribute(): string
    {
        return $this->attributeValues()
            ->orderBy('id')
            ->pluck('id')
            ->implode('-');
    }

    /**
     * URL ảnh biến thể (media uuid hoặc URL), cùng pattern ProductImage.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $this->image)) {
            $media = Media::where('uuid', $this->image)->first();

            return $media?->getUrl();
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
