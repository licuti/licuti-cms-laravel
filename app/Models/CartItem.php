<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'cart_items';

    protected $fillable = [
        'uuid',
        'cart_id',
        'product_id',
        'product_variant_id',
        'line_key',
        'quantity',
        'unit_price_snapshot',
        'meta',
    ];

    protected $casts = [
        'quantity'            => 'integer',
        'unit_price_snapshot' => 'decimal:2',
        'meta'                => 'array',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public static function makeLineKey(int $productId, ?int $variantId): string
    {
        return "p{$productId}-v" . ($variantId ?: 0);
    }
}
