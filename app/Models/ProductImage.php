<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'image',
        'is_primary',
        'display_order',
    ];

    protected $casts = [
        'is_primary'    => 'boolean',
        'display_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image', 'uuid');
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $this->image)) {
            $media = $this->media;
            return $media?->url ?? ($media?->path ? asset('storage/' . $media->path) : null);
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
