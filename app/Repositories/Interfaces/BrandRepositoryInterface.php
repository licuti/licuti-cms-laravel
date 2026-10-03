<?php

namespace App\Repositories\Interfaces;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Brand;

    public function getActiveBrands();

    public function getForSelect();

    public function clearCache(): void;
}
