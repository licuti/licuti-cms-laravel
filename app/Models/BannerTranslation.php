<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerTranslation extends Model
{
    use HasFactory;

    protected $table = 'banner_translations';

    protected $fillable = [
        'banner_id',
        'locale',
        'title',
        'image',
        'description',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $this->image)) {
            $media = $this->imageMedia;
            return $media?->url ?? ($media?->path ? asset('storage/' . $media->path) : null);
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
