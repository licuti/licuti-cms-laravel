<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    const CACHE_KEY_SELECT = 'categories:select';

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['translations', 'parent.translations', 'imageMedia']);

        if (! empty($filters['keyword'])) {
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

        if (! empty($filters['keyword'])) {
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
        // return Cache::tags(['categories'])->rememberForever('categories_active_tree', function () {
        return $this->model->with(['translations', 'parent.translations', 'imageMedia'])->where('is_active', true)->orderBy('display_order')->get();
        // });
    }

    /**
     * Danh sách danh mục kèm translation cho form select (Product, filter...).
     * Cache vì đổi ít, đọc nhiều — invalidate qua CategoryObserver.
     */
    public function getForSelect()
    {
        return Cache::rememberForever(self::CACHE_KEY_SELECT, function () {
            return $this->model->with('translations')->orderBy('display_order')->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_SELECT);
    }
}
