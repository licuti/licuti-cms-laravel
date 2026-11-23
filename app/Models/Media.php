<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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

        static::deleted(function ($media) {
            $deleteFiles = function () use ($media): void {
                $disk = Storage::disk($media->disk);
                $paths = array_filter([$media->file_path, $media->thumb_path]);
                $disk->delete($paths);
            };

            if (DB::transactionLevel() > 0) {
                DB::afterCommit($deleteFiles);
            } else {
                $deleteFiles();
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
