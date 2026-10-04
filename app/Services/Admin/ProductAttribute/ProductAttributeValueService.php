<?php

namespace App\Services\Admin\ProductAttribute;

use App\Core\Base\BaseService;
use App\DTOs\ProductAttribute\ProductAttributeValueDTO;
use App\Models\ProductAttributeValue;
use App\Repositories\Interfaces\ProductAttributeValueRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeValueService extends BaseService
{
    public function __construct(
        private readonly ProductAttributeValueRepositoryInterface $repository,
        private readonly ProductAttributeRepositoryInterface $attributeRepository
    ) {
    }

    public function getList(int $attributeId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginatedByAttribute($attributeId, $filters);
    }

    public function create(ProductAttributeValueDTO $dto): ProductAttributeValue
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);

            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['value'])) {
                    $model->translations()->create([
                        'locale' => $locale,
                        'value'  => $transData['value'],
                    ]);
                }
            }

            $this->attributeRepository->clearCache();
            return $model;
        });
    }

    public function update(string $uuid, ProductAttributeValueDTO $dto): ProductAttributeValue
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['value'])) {
                    $model->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['value' => $transData['value']]
                    );
                } else {
                    $model->translations()->where('locale', $locale)->delete();
                }
            }

            $this->attributeRepository->clearCache();
            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            
            // Check if value is used in products
            if ($model->products()->exists()) {
                throw new \Exception(__('Không thể xóa giá trị này vì đang được sử dụng trong sản phẩm.'));
            }

            $model->translations()->delete();
            $result = $this->repository->delete($model->id);
            $this->attributeRepository->clearCache();
            return $result;
        });
    }
}
