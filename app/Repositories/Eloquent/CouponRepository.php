<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Coupon;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();
        
        $search = $filters['search'] ?? $filters['keyword'] ?? null;
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        
        $status = $filters['status'] ?? $filters['is_active'] ?? null;
        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool)$status);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }
    
    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->first();
    }
    
    public function findByUuidOrFail(string $uuid): Coupon
    {
        return $this->findByUuid($uuid);
    }

    public function incrementUsedCount(int $couponId): void
    {
        $this->model->where('id', $couponId)->increment('used_count');
    }

    public function deleteByIds(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function updateStatusByIds(array $ids, bool $isActive): int
    {
        return $this->model->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    public function releaseUsageByOrder(int $orderId): int
    {
        $usages = \Illuminate\Support\Facades\DB::table('coupon_users')
            ->where('order_id', $orderId)
            ->get();

        if ($usages->isEmpty()) {
            return 0;
        }

        foreach ($usages as $usage) {
            $this->model->where('id', $usage->coupon_id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }

        return \Illuminate\Support\Facades\DB::table('coupon_users')
            ->where('order_id', $orderId)
            ->delete();
    }
}
