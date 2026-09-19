<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    public function getAvailableActionsAttribute(): array
    {
        $uuid = $this->uuid ?? (string) $this->id;

        return [
            [
                'label'  => __('Sửa'),
                'route'  => route('admin.tags.edit', $uuid),
                'method' => 'GET',
                'color'  => 'blue',
            ],
            [
                'label'         => __('Xóa'),
                'route'         => route('admin.tags.destroy', $uuid),
                'method'        => 'DELETE',
                'color'         => 'red',
                'confirm_title' => __('Xóa thẻ tag?'),
                'confirm_text'  => __('Thao tác này sẽ gỡ thẻ tag này khỏi các bài viết liên quan.'),
                'confirm_btn'   => __('Xóa ngay'),
            ],
        ];
    }
}
