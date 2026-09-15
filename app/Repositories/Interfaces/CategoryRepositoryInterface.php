<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Category;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function getAllActive();
}
