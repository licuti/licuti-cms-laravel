<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Models\PostCategory;

class PostCategoryRepository extends BaseRepository implements PostCategoryRepositoryInterface
{
    public function __construct(PostCategory $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['translations', 'parent.translations', 'imageMedia']);

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('slug', 'LIKE', "%{$keyword}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getAllForList(array $filters = [])
    {
        $query = $this->model->newQuery()->with(['translations', 'imageMedia']);

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->whereHas('translations', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('slug', 'LIKE', "%{$keyword}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status']);
        }

        return $query->orderBy('display_order')->orderBy('created_at', 'desc')->get();
    }

    public function getAllActive()
    {
        // TODO (Future): Enable Cache tags when observer clears them
        // return Cache::tags(['post_categories'])->rememberForever('post_categories_active_tree', function () {
            return $this->model->with(['translations', 'parent.translations', 'imageMedia'])->where('is_active', true)->orderBy('display_order')->get();
        // });
    }

    public function getTree(): \Illuminate\Support\Collection
    {
        $all = $this->model->with('translations')->orderBy('display_order')->get();

        $buildBranch = function (?string $parentId) use ($all, &$buildBranch) {
            return $all
                ->filter(fn ($item) => (string) $item->parent_id === (string) $parentId
                    || ($parentId === null && $item->parent_id === null))
                ->map(function ($item) use (&$buildBranch) {
                    $item->_children = $buildBranch($item->id);
                    return $item;
                })
                ->values();
        };

        return $buildBranch(null);
    }

    public function getStats(): array
    {
        $counts = $this->model->newQuery()
            ->selectRaw('COUNT(*) AS total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive')
            ->first();

        return [
            'total'    => (int) ($counts->total ?? 0),
            'active'   => (int) ($counts->active ?? 0),
            'inactive' => (int) ($counts->inactive ?? 0),
        ];
    }
}
