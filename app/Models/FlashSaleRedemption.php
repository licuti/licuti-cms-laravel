<?php

namespace App\Models;

use App\Enums\RedemptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleRedemption extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_redemptions';

    protected $fillable = [
        'flash_sale_product_id',
        'order_item_id',
        'user_id',
        'guest_email',
        'quantity',
        'status',
        'released_at',
    ];

    protected $casts = [
        'status'      => RedemptionStatus::class,
        'quantity'    => 'integer',
        'released_at' => 'datetime',
    ];

    public function flashSaleProduct(): BelongsTo
    {
        return $this->belongsTo(FlashSaleProduct::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
