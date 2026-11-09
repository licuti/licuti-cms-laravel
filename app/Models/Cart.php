<?php

namespace App\Models;

use App\Enums\CartStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'carts';

    protected $fillable = [
        'uuid',
        'user_id',
        'coupon_id',
        'guest_email',
        'currency',
        'status',
        'converted_order_id',
        'last_activity_at',
    ];

    protected $casts = [
        'status'           => CartStatus::class,
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CartStatus::ACTIVE->value);
    }

    public function scopeAbandoned(Builder $query): Builder
    {
        return $query->where('status', CartStatus::ABANDONED->value);
    }

    public function scopeForOwner(Builder $query, ?int $userId, ?string $token): Builder
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        if ($token) {
            return $query->where('uuid', $token)->whereNull('user_id');
        }

        return $query->whereRaw('1 = 0');
    }

    public function touchActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}