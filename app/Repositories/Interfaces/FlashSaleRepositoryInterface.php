<?php

namespace App\Repositories\Interfaces;

use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FlashSaleRepositoryInterface extends BaseRepositoryInterface
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidOrFail(string $uuid): FlashSale;

    public function getActiveRunning(): Collection;

    public function getStatusCounts(): array;

    public function deleteByIds(array $ids): int;
}
