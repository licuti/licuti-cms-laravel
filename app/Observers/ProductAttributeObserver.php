<?php

namespace App\Observers;

use App\Models\ProductAttribute;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;

/**
 * P3.3 — Invalidate cache catalog thuộc tính khi attribute thay đổi.
 *
 * Thay đổi value (ProductAttributeValue) được cover bởi
 * ProductAttributeValueObserver. Ngoại lệ: các thao tác không fire model
 * event (mass delete qua deleteCascade, DB::table update trong moveValue)
 * vẫn gọi clearCache() tay tại chỗ.
 */
class ProductAttributeObserver
{
    public function __construct(private readonly ProductAttributeRepositoryInterface $repository) {}

    public function saved(ProductAttribute $attribute): void
    {
        $this->repository->clearCache();
    }

    public function deleted(ProductAttribute $attribute): void
    {
        $this->repository->clearCache();
    }
}
