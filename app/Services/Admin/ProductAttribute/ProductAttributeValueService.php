<?php

namespace App\Services\Admin\ProductAttribute;

use App\Core\Base\BaseService;
use App\DTOs\ProductAttribute\ProductAttributeValueDTO;
use App\Models\ProductAttributeValue;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeValueRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductAttributeValueService extends BaseService
{
    public function __construct(
        private readonly ProductAttributeValueRepositoryInterface $repository,
        private readonly ProductAttributeRepositoryInterface $attributeRepository
    ) {}

    public function getList(int $attributeId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginatedByAttribute($attributeId, $filters);
    }

    public function create(ProductAttributeValueDTO $dto): ProductAttributeValue
    {
        return DB::transaction(function () use ($dto) {
            $model = $this->repository->create($dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (! empty($transData['value'])) {
                    $model->translations()->create([
                        'locale' => $locale,
                        'value' => $transData['value'],
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, ProductAttributeValueDTO $dto): ProductAttributeValue
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (! empty($transData['value'])) {
                    $model->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['value' => $transData['value']]
                    );
                } else {
                    $model->translations()->where('locale', $locale)->delete();
                }
            }

            return $model->fresh(['translations']);
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

            return $this->repository->delete($model->id);
        });
    }

    /**
     * Đổi chỗ value với value kề cùng attribute (display_order asc, id asc).
     * Không có sibling (đầu/cuối) thì không làm gì.
     */
    public function moveValue(string $attributeUuid, string $uuid, string $direction): void
    {
        DB::transaction(function () use ($attributeUuid, $uuid, $direction) {
            $attribute = $this->attributeRepository->findByUuid($attributeUuid);
            $model = $this->repository->findByUuidAndAttribute($uuid, $attribute->id);

            $query = ProductAttributeValue::where('attribute_id', $attribute->id)
                ->orderBy('display_order')
                ->orderBy('id');

            if ($direction === 'up') {
                $sibling = (clone $query)
                    ->where(function ($q) use ($model) {
                        $q->where('display_order', '<', $model->display_order)
                            ->orWhere(function ($q2) use ($model) {
                                $q2->where('display_order', $model->display_order)
                                    ->where('id', '<', $model->id);
                            });
                    })
                    ->orderBy('display_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $sibling = (clone $query)
                    ->where(function ($q) use ($model) {
                        $q->where('display_order', '>', $model->display_order)
                            ->orWhere(function ($q2) use ($model) {
                                $q2->where('display_order', $model->display_order)
                                    ->where('id', '>', $model->id);
                            });
                    })
                    ->orderBy('display_order')
                    ->orderBy('id')
                    ->first();
            }

            if (is_null($sibling)) {
                return;
            }

            // Swap display_order bằng raw update để tránh model event lặp.
            $firstOrder = $model->display_order;
            $secondOrder = $sibling->display_order;

            DB::table('product_attribute_values')->where('id', $model->id)->update(['display_order' => $secondOrder]);
            DB::table('product_attribute_values')->where('id', $sibling->id)->update(['display_order' => $firstOrder]);

            // Raw update không fire model event → observer không chạy, phải clear tay.
            $this->attributeRepository->clearCache();
        });
    }
}
