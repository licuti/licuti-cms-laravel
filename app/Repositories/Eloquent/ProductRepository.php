<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    const CACHE_KEY_PRODUCT_PREFIX = 'product:';

    const CACHE_KEY_FEATURED = 'products:featured';

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with([
            'translations',
            'category.translations',
            'brand.translations',
            'images.media',
            'primaryImageMedia',
        ]);

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', $search)
                    ->orWhere('barcode', 'like', $search)
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('name', 'like', $search)
                            ->orWhere('slug', 'like', $search);
                    });
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Lọc theo tab (all | published | draft | archived)
        // 'tab' là filter duy nhất cho status — filter-tabs component gửi ?tab=
        if (! empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }

        return $query->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid): ?Product
    {
        return $this->model->with([
            'translations',
            'category.translations',
            'brand.translations',
            'images.media',
            'primaryImageMedia',
            'seoTranslations',
            'attributes.values',
            'attributeValues',
            'variants.attributeValues.attribute',
            'tags',
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function updateStatusByIds(array $ids, string $status): int
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Lấy product với row lock (SELECT ... FOR UPDATE) — dùng cho atomic stock.
     */
    public function lockForUpdateFind(int $id): ?Product
    {
        return $this->model->lockForUpdate()->find($id);
    }

    /**
     * Xóa cache liên quan đến product (gọi từ ProductObserver và bulk path).
     *
     * CACHE_STORE mặc định = database → không hỗ trợ cache tags, dùng key-based.
     */
    public function clearCache(?Product $product = null): void
    {
        Cache::forget(self::CACHE_KEY_FEATURED);

        if ($product) {
            Cache::forget(self::CACHE_KEY_PRODUCT_PREFIX.$product->uuid);
        }
    }
}
