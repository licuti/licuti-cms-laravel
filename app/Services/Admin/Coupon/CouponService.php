<?php

namespace App\Services\Admin\Coupon;

use App\Core\Base\BaseService;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CouponService extends BaseService
{
    public function __construct(
        private readonly CouponRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters);
    }

    public function create(array $data): Coupon
    {
        return DB::transaction(function () use ($data) {
            $data['uuid'] = Str::uuid()->toString();
            // Xử lý is_active, cannot_combine_with_sale_items, stackable từ checkbox form
            $data['is_active'] = isset($data['is_active']);
            $data['cannot_combine_with_sale_items'] = isset($data['cannot_combine_with_sale_items']);
            $data['stackable'] = isset($data['stackable']);
            
            return $this->repository->create($data);
        });
    }

    public function update(string $uuid, array $data): Coupon
    {
        return DB::transaction(function () use ($uuid, $data) {
            $model = $this->repository->findByUuidOrFail($uuid);
            
            $data['is_active'] = isset($data['is_active']);
            $data['cannot_combine_with_sale_items'] = isset($data['cannot_combine_with_sale_items']);
            $data['stackable'] = isset($data['stackable']);
            
            $this->repository->update($model->id, $data);
            return $model->refresh();
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuidOrFail($uuid);
        return $this->repository->delete($model->id);
    }
}
