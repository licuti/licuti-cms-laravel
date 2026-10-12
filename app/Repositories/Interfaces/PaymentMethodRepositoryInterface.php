<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PaymentMethod;

interface PaymentMethodRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCodes(): array;

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function updateStatusByIds(array $ids, bool $isActive): int;

    public function deleteByIds(array $ids): int;
}
