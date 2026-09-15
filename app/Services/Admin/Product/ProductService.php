<?php

namespace App\Services\Admin\Product;

use App\Core\Base\BaseService;
use App\DTOs\Product\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated();
    }

    public function create(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            foreach ($dto->translations as $locale => $transData) {
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);
                $model->translations()->create(array_merge($transData, ['locale' => $locale]));
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function update(string $uuid, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $this->repository->update($model->id, $dto->toArray());
            foreach ($dto->translations as $locale => $transData) {
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);
                $model->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $transData
                );
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
