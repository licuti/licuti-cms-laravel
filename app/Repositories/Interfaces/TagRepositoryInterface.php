<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function getFiltered(array $filters = []): LengthAwarePaginator;
}
