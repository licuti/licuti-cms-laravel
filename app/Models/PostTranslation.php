<?php

namespace App\Models;

use Database\Factories\PostTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTranslation extends Model
{
    /** @use HasFactory<PostTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'post_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
