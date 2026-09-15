<?php

namespace App\Services\Admin\Brand;

use App\Core\Base\BaseService;
use App\DTOs\Brand\BrandDTO;
use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BrandService extends BaseService
{
    public function __construct(
        private readonly BrandRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated();
    }

    public function create(BrandDTO $dto): Brand
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            foreach ($dto->translations as $locale => $transData) {
                $model->translations()->create(array_merge($transData, ['locale' => $locale]));
            }
            return $model;
        });
    }

    public function update(string $uuid, BrandDTO $dto): Brand
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $this->repository->update($model->id, $dto->toArray());
            foreach ($dto->translations as $locale => $transData) {
                $model->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $transData
                );
            }
            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
