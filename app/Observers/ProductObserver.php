<?php

namespace App\Observers;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

/**
 * Invalidate cache product khi có thay đổi (groundwork cho storefront + Cart/Order).
 *
 * Lưu ý: observer KHÔNG fire khi xóa qua bulk query (`deleteByIds`) — service
 * phải gọi `clearCache()` tay ở đường đó.
 */
class ProductObserver
{
    public function __construct(private readonly ProductRepositoryInterface $repository) {}

    public function saved(Product $product): void
    {
        $this->repository->clearCache($product);
    }

    public function deleted(Product $product): void
    {
        $this->repository->clearCache($product);
    }
}
