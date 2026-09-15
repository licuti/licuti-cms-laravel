<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

/**
 * Tự động sinh UUID khi tạo bản ghi mới.
 * 
 * Cách dùng: use HasUuid; trong Model
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Lấy route key theo uuid thay vì id.
     * Giúp route model binding dùng uuid tự động.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
