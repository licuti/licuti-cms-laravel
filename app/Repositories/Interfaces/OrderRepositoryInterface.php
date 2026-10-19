<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function findByUuidWithRelations(string $uuid): Order;

    public function getStatusCounts(): array;
}
