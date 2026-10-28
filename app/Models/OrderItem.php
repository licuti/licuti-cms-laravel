<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'order_id',
        'product_id',
        'product_variant_id',
        'flash_sale_product_id',
        'product_name',
        'product_sku',
        'unit_price',
        'quantity',
        'discount_amount',
        'subtotal',
        'meta',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'quantity'        => 'integer',
        'meta'            => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function flashSaleProduct(): BelongsTo
    {
        return $this->belongsTo(FlashSaleProduct::class);
    }
}
