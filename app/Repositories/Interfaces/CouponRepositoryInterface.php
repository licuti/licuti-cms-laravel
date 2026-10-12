<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Coupon;

interface CouponRepositoryInterface extends BaseRepositoryInterface
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findByCode(string $code): ?Coupon;
    public function findByUuidOrFail(string $uuid): Coupon;
    public function deleteByIds(array $ids): int;
    public function updateStatusByIds(array $ids, bool $isActive): int;
    public function incrementUsedCount(int $couponId): void;
}
