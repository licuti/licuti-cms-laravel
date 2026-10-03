<?php

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->withCount('posts');

        if (! empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(5, min(100, $perPage));

        return $query->orderBy('display_order')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Danh sách tag đang hoạt động, sắp xếp theo tên — dùng cho select tag
     * ở form sản phẩm / bài viết.
     */
    public function getActiveOrdered(): Collection
    {
        return $this->model->orderBy('name')->get(['id', 'name', 'slug']);
    }
}
