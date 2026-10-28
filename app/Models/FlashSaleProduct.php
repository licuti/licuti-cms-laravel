<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSaleProduct extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_products';

    protected $fillable = [
        'flash_sale_id',
        'product_id',
        'product_variant_id',
        'variant_key',
        'flash_price',
        'quantity_limit',
        'sold_quantity',
        'limit_per_user',
        'sort_order',
    ];

    protected $casts = [
        'flash_price'    => 'decimal:2',
        'quantity_limit' => 'integer',
        'sold_quantity'  => 'integer',
        'limit_per_user' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(FlashSaleRedemption::class);
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity_limit - $this->sold_quantity);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->sold_quantity >= $this->quantity_limit;
    }

    public function getSoldPercentageAttribute(): int
    {
        if ($this->quantity_limit <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->sold_quantity / $this->quantity_limit) * 100));
    }
}
