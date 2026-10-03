<?php

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    const CACHE_KEY_SELECT = 'brands:select';

    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'logoMedia']);

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('website', 'like', $search)
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('name', 'like', $search)
                            ->orWhere('slug', 'like', $search);
                    });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->orderBy('display_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findByUuidWithRelations(string $uuid): ?Brand
    {
        return $this->model->with(['translations', 'logoMedia', 'seoTranslations'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Lấy các thương hiệu đang hoạt động để hiển thị ở form chọn (Product...).
     */
    public function getActiveBrands()
    {
        return $this->model->with('translations')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->latest('id')
            ->get();
    }

    /**
     * Danh sách thương hiệu kèm translation cho form select (Product, filter...).
     * Cache vì đổi ít, đọc nhiều — invalidate qua BrandObserver.
     */
    public function getForSelect()
    {
        return Cache::rememberForever(self::CACHE_KEY_SELECT, function () {
            return $this->model->with('translations')
                ->orderBy('display_order', 'asc')
                ->latest('id')
                ->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_SELECT);
    }
}
