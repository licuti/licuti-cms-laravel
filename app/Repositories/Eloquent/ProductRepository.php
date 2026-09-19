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
            'primaryImage.media',
            'images.media'
        ]);

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search)
                  ->orWhereHas('translations', function ($tq) use ($search) {
                      $tq->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search);
                  });
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
            'seoTranslations'
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }
}
