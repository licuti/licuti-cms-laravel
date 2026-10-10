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
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }
    
    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->first();
    }
    
    public function incrementUsedCount(int $couponId): void
    {
        $this->model->where('id', $couponId)->increment('used_count');
    }
}
