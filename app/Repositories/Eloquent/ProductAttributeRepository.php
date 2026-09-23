<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductAttribute;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductAttributeRepository extends BaseRepository implements ProductAttributeRepositoryInterface
{
    public function __construct(ProductAttribute $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'values'])->global();

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', $search)
                  ->orWhereHas('translations', function ($tq) use ($search) {
                      $tq->where('name', 'like', $search);
                  });
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid): ?ProductAttribute
    {
        return $this->model->with(['translations', 'values'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Lấy các thuộc tính đang hoạt động kèm giá trị (dùng cho form biến thể sản phẩm / bộ lọc).
     */
    public function getActiveWithValues()
    {
        return $this->model->with(['translations', 'values'])
            ->global()
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Catalog toàn cục + thuộc tính custom của product $productId.
     */
    public function getAvailableForProduct(int $productId)
    {
        return $this->model->with(['translations', 'values'])
            ->where(function ($q) use ($productId) {
                $q->whereNull('product_id')->orWhere('product_id', $productId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
