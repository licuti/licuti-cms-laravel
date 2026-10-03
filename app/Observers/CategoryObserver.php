<?php

namespace App\Observers;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

/**
 * P3.3 — Invalidate cache select danh mục khi category thay đổi.
 */
class CategoryObserver
{
    public function __construct(private readonly CategoryRepositoryInterface $repository) {}

    public function saved(Category $category): void
    {
        $this->repository->clearCache();
    }

    public function deleted(Category $category): void
    {
        $this->repository->clearCache();
    }
}
