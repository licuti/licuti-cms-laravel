<?php

namespace App\Observers;

use App\Models\ProductAttributeValue;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;

/**
 * Invalidate cache catalog thuộc tính khi một value thay đổi.
 *
 * Bổ sung cho ProductAttributeObserver: trước đây clear cache chỉ chạy qua
 * các l gọi tay trong service/repository, dễ bỏ sót khi có code path mới.
 * Observer fire cho create/update/delete qua Eloquent → bao phủ hết.
 *
 * Ngoại lệ đã biết: mass delete/query builder update (VD: deleteCascade,
 * moveValue dùng DB::table) không fire event → các chỗ đó vẫn gọi clearCache() tay.
 */
class ProductAttributeValueObserver
{
    public function __construct(private readonly ProductAttributeRepositoryInterface $repository) {}

    public function saved(ProductAttributeValue $value): void
    {
        $this->repository->clearCache();
    }

    public function deleted(ProductAttributeValue $value): void
    {
        $this->repository->clearCache();
    }
}
