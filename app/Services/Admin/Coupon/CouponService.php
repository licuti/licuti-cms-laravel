<?php

namespace App\Services\Admin\Coupon;

use App\Core\Base\BaseService;
use App\DTOs\Coupon\CouponDTO;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function create(CouponDTO|array $data): Coupon
    {
        return $this->handleTransaction(function () use ($data) {
            $payload = $data instanceof CouponDTO ? $data->toArray() : $data;

            return $this->repository->create($payload);
        });
    }

    public function update(string $uuid, CouponDTO|array $data): Coupon
    {
        return $this->handleTransaction(function () use ($uuid, $data) {
            $model = $this->repository->findByUuidOrFail($uuid);
            $payload = $data instanceof CouponDTO ? $data->toArray() : $data;

            $this->repository->update($model->id, $payload);
            return $model->refresh();
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuidOrFail($uuid);
        return $this->repository->delete($model->id);
    }
}
