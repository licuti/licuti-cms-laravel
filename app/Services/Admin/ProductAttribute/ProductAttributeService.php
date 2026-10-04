<?php

namespace App\Services\Admin\ProductAttribute;

use App\Core\Base\BaseService;
use App\DTOs\ProductAttribute\ProductAttributeDTO;
use App\Models\ProductAttribute;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeService extends BaseService
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    public function create(ProductAttributeDTO $dto): ProductAttribute
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();

            $model = $this->repository->create($data);

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['name'])) {
                    $model->translations()->create([
                        'locale' => $locale,
                        'name'   => $transData['name'],
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, ProductAttributeDTO $dto): ProductAttribute
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['name'])) {
                    $model->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['name' => $transData['name']]
                    );
                }
            }

            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return $this->handleTransaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            if ($model->products()->exists()) {
                throw new \RuntimeException(__('Không thể xóa thuộc tính đang được sử dụng trong sản phẩm.'));
            }

            return $this->repository->deleteCascade($model);
        });
    }
}
