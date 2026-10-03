<?php

namespace App\Observers;

use App\Models\ProductAttribute;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;

/**
 * P3.3 — Invalidate cache catalog thuộc tính khi attribute thay đổi.
 *
 * Lưu ý: thay đổi value (ProductAttributeValue) ngoài repository (VD: service
 * update trực tiếp) cũng cần clear cache — đã cover qua
 * `ProductAttributeRepository::createValue()` và việc attribute save thường
 * đi kèm trong cùng transaction.
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
