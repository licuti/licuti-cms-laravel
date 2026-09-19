<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, HasUuid, HasSeo, SoftDeletes;

    protected $table = 'brands';

    protected $fillable = [
        'uuid',
        'logo',
        'website',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(BrandTranslation::class, 'brand_id');
    }

    public function translate(?string $locale = null): ?BrandTranslation
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

    public function getSlugAttribute(): string
    {
        return $this->translate()?->slug ?? '';
    }

    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo', 'uuid');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $this->logo)) {
            $media = Media::where('uuid', $this->logo)->first();
            return $media?->url;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return asset('storage/' . $this->logo);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? __('Đang hoạt động') : __('Tạm ẩn');
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }
}