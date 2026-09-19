<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Product;
}
