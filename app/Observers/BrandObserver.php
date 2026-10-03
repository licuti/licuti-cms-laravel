<?php

namespace App\Observers;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;

/**
 * P3.3 — Invalidate cache select thương hiệu khi brand thay đổi.
 */
class BrandObserver
{
    public function __construct(private readonly BrandRepositoryInterface $repository) {}

    public function saved(Brand $brand): void
    {
        $this->repository->clearCache();
    }

    public function deleted(Brand $brand): void
    {
        $this->repository->clearCache();
    }
}
