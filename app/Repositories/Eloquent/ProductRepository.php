<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
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
            'seoTranslations',
            'attributes.values',
            'attributeValues',
            'variants.attributeValues.attribute',
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
