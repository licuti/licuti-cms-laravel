<?php

namespace App\Models;

use Database\Factories\PageTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    /** @use HasFactory<PageTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
    ];

    protected $casts = [
        'page_id' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}