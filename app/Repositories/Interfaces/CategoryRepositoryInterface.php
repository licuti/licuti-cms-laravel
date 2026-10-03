<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getAllActive();

    public function getForSelect();

    public function clearCache(): void;
}
