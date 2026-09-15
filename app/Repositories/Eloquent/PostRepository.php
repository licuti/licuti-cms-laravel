<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'categories.translations', 'author', 'imageMedia']);

        // Lọc theo tab (all | published | draft | archived)
        // 'tab' là filter duy nhất cho status — controller chỉ truyền 1 trong 2
        if (!empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        // Lọc theo danh mục
        if (!empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('post_categories.id', $filters['category']);
            });
        }

        // Tìm theo tiêu đề hoặc slug (mọi locale)
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(5, min(100, $perPage)); // giới hạn 5-100

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function updateStatusByIds(array $ids, string $status): int
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
