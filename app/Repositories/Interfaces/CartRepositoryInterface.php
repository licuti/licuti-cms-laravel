<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function findActiveByUserId(int $userId): ?Cart;

    public function findActiveByUuid(string $uuid): ?Cart;

    public function findByUuidWithItems(string $uuid): ?Cart;

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function markAbandoned(Carbon $threshold): int;

    public function purgeStale(Carbon $guestThreshold, Carbon $userThreshold): int;

    public function countByStatus(): array;
}
