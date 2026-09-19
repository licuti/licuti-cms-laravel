<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'banners';

    protected $fillable = [
        'uuid',
        'position',
        'link',
        'target',
        'display_order',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
        'start_date'    => 'datetime',
        'end_date'      => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(BannerTranslation::class, 'banner_id');
    }

    public function translate(?string $locale = null): ?BannerTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'vi'))
            ?? $this->translations->first();
    }

    public function getTitleAttribute(): string
    {
        return $this->translate()?->title ?? '';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->translate()?->image_url;
    }
}
