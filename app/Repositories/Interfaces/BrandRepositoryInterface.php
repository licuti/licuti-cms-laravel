<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Brand;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Brand;

    public function getActiveBrands();
}
