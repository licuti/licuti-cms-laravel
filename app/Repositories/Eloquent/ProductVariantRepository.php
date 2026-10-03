<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;

class ProductVariantRepository extends BaseRepository implements ProductVariantRepositoryInterface
{
    public function __construct(ProductVariant $model)
    {
        parent::__construct($model);
    }

    /**
     * Lấy variant với row lock (SELECT ... FOR UPDATE) — dùng cho atomic stock.
     */
    public function lockForUpdateFind(int $id): ?ProductVariant
    {
        return $this->model->lockForUpdate()->find($id);
    }
}
