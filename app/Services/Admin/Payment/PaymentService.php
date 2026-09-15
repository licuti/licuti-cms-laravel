<?php

namespace App\Services\Admin\Payment;

use App\Core\Base\BaseService;
use App\DTOs\Payment\PaymentDTO;
use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentService extends BaseService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated();
    }

    public function create(PaymentDTO $dto): Payment
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            return $model;
        });
    }

    public function update(string $uuid, PaymentDTO $dto): Payment
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $this->repository->update($model->id, $dto->toArray());
            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
