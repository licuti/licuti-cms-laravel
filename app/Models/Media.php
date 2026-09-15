<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'folder_id',
        'user_id',
        'disk',
        'file_path',
        'thumb_path',
        'file_name',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            // Delete physical files
            if (Storage::disk($media->disk)->exists($media->file_path)) {
                Storage::disk($media->disk)->delete($media->file_path);
            }
            if ($media->thumb_path && Storage::disk($media->disk)->exists($media->thumb_path)) {
                Storage::disk($media->disk)->delete($media->thumb_path);
            }
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function getThumbUrl(): string
    {
        if ($this->thumb_path) {
            return Storage::disk($this->disk)->url($this->thumb_path);
        }
        return $this->getUrl();
    }
}
