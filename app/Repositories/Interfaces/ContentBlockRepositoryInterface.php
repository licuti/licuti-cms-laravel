<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface ContentBlockRepositoryInterface extends BaseRepositoryInterface
{
    public function getFiltered(array $filters): LengthAwarePaginator;

    public function updateStatusByIds(array $ids, string $status): int;

    public function deleteByIds(array $ids): int;

    public function countByStatus(): array;
}
