<?php

namespace App\Models;

use App\Core\Enums\CouponType;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Coupon extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_value',
        'max_discount_amount',
        'usage_limit',
        'usage_per_user',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'cannot_combine_with_sale_items',
        'stackable',
    ];

    protected $casts = [
        'type' => CouponType::class,
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'cannot_combine_with_sale_items' => 'boolean',
        'stackable' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_users')
            ->withPivot(['guest_email', 'order_id', 'discount_amount', 'used_at'])
            ->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUser::class);
    }
}