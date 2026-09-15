<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_category_id',
        'locale',
        'name',
        'slug',
        'description',
    ];

    public function postCategory(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}