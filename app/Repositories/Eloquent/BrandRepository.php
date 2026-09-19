<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Brand;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'logoMedia']);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
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
}
