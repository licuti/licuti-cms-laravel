<?php

namespace App\Services\Admin\FlashSale;

use App\Core\Base\BaseService;
use App\DTOs\FlashSale\FlashSaleDTO;
use App\Models\FlashSale;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FlashSaleService extends BaseService
{
    public function __construct(
        private readonly FlashSaleRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated();
    }

    public function create(FlashSaleDTO $dto): FlashSale
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            return $model;
        });
    }

    public function update(string $uuid, FlashSaleDTO $dto): FlashSale
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
