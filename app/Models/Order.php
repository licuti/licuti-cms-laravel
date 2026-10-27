<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'payment_method_id',
        'coupon_id',
        'coupon_code',
        'status',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'tax_amount',
        'total_amount',
        'currency',
        'notes',
        'cancel_reason',
        'paid_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status'          => OrderStatus::class,
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee'    => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_at'         => 'datetime',
        'shipped_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->currency)) {
                $order->currency = 'VND';
            }
            if (empty($order->status)) {
                $order->status = OrderStatus::PENDING;
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopeByStatus(Builder $query, string|OrderStatus $status): Builder
    {
        $value = $status instanceof OrderStatus ? $status->value : $status;
        return $query->where('status', $value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('order_number', 'like', "%{$term}%")
              ->orWhereHas('shippingAddress', function (Builder $sub) use ($term) {
                  $sub->where('recipient_name', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
              })
              ->orWhereHas('user', function (Builder $sub) use ($term) {
                  $sub->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
              });
        });
    }

    // Helpers
    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    public function canCancel(): bool
    {
        return $this->status->canTransitionTo(OrderStatus::CANCELLED);
    }

    public function hasAvailableTransitions(): bool
    {
        foreach (OrderStatus::cases() as $status) {
            if ($this->status->canTransitionTo($status) && $status !== $this->status) {
                return true;
            }
        }

        return false;
    }
}