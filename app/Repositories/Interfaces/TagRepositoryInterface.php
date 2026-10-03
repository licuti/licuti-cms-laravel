<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function getFiltered(array $filters = []): LengthAwarePaginator;

    public function getActiveOrdered(): Collection;
}
