<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seo_metadata';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'locale',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'focus_keyword',
        'seo_score',
        'schema_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'seo_score' => 'integer',
    ];

    /**
     * Get the parent seoable model (post, product, category, etc.).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
