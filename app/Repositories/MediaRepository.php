<?php

namespace App\Repositories;

use App\Repositories\Interfaces\MediaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Media;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedMedia(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        
        if (isset($filters['sort']) && $filters['sort'] === 'asc') {
            $query->oldest();
        } else {
            $query->latest();
        }

        if (array_key_exists('folder_id', $filters)) {
            if ($filters['folder_id'] === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $filters['folder_id']);
            }
        }

        if (!empty($filters['mime_type'])) {
            if (str_contains($filters['mime_type'], '/')) {
                $query->where('mime_type', $filters['mime_type']);
            } else {
                $query->where('mime_type', 'like', $filters['mime_type'] . '/%');
            }
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . trim($filters['keyword']) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('original_name', 'like', $keyword)
                  ->orWhere('file_name', 'like', $keyword);
            });
        }

        return $query->paginate($perPage);
    }
}
