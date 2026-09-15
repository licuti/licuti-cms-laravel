<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface MediaRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginatedMedia(array $filters = [], int $perPage = 20): LengthAwarePaginator;
}
