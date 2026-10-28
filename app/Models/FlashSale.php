<?php

namespace App\Models;

use App\Enums\FlashSaleStatus;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlashSale extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'flash_sales';

    protected $fillable = [
        'uuid',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(FlashSaleTranslation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function translate(?string $locale = null): ?FlashSaleTranslation
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'vi'))
            ?? $this->translations->first();
    }

    public function getNameAttribute(): string
    {
        return $this->translate()?->name ?? '';
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translate()?->description;
    }

    public function getStatusAttribute(): FlashSaleStatus
    {
        if (! $this->is_active) {
            return FlashSaleStatus::DRAFT;
        }

        $now = Carbon::now();
        if ($now->lt($this->starts_at)) {
            return FlashSaleStatus::SCHEDULED;
        }

        if ($now->gt($this->ends_at)) {
            return FlashSaleStatus::ENDED;
        }

        return FlashSaleStatus::RUNNING;
    }

    public function isRunning(): bool
    {
        return $this->status === FlashSaleStatus::RUNNING;
    }

    public function hasStarted(): bool
    {
        return Carbon::now()->gte($this->starts_at);
    }

    public function scopeActiveWindow(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        $now = Carbon::now();

        return match ($status) {
            FlashSaleStatus::DRAFT->value => $query->where('is_active', false),
            FlashSaleStatus::SCHEDULED->value => $query->where('is_active', true)->where('starts_at', '>', $now),
            FlashSaleStatus::RUNNING->value => $query->where('is_active', true)->where('starts_at', '<=', $now)->where('ends_at', '>=', $now),
            FlashSaleStatus::ENDED->value => $query->where('is_active', true)->where('ends_at', '<', $now),
            default => $query,
        };
    }
}