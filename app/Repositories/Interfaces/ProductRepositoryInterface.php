<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Product;

    public function countByStatus(): array;

    public function deleteByIds(array $ids): int;

    public function updateStatusByIds(array $ids, string $status): int;

    public function lockForUpdateFind(int $id): ?Product;

    public function clearCache(?Product $product = null): void;
}
