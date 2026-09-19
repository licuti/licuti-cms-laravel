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
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

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

            // Values
            foreach ($dto->values as $index => $valItem) {
                if (!empty($valItem['value'])) {
                    $model->values()->create([
                        'uuid'          => Str::uuid()->toString(),
                        'value'         => $valItem['value'],
                        'color_code'    => $valItem['color_code'] ?? null,
                        'display_order' => $valItem['display_order'] ?? $index,
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, ProductAttributeDTO $dto): ProductAttribute
    {
        return DB::transaction(function () use ($uuid, $dto) {
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

            // Values synchronization
            $submittedValues = $dto->values;
            $existingValueIds = [];

            foreach ($submittedValues as $index => $valItem) {
                if (empty($valItem['value'])) {
                    continue;
                }

                if (!empty($valItem['uuid'])) {
                    $existingVal = $model->values()->where('uuid', $valItem['uuid'])->first();
                    if ($existingVal) {
                        $existingVal->update([
                            'value'         => $valItem['value'],
                            'color_code'    => $valItem['color_code'] ?? null,
                            'display_order' => $valItem['display_order'] ?? $index,
                        ]);
                        $existingValueIds[] = $existingVal->id;
                        continue;
                    }
                }

                $newVal = $model->values()->create([
                    'uuid'          => Str::uuid()->toString(),
                    'value'         => $valItem['value'],
                    'color_code'    => $valItem['color_code'] ?? null,
                    'display_order' => $valItem['display_order'] ?? $index,
                ]);
                $existingValueIds[] = $newVal->id;
            }

            // Delete values that are no longer present
            $model->values()->whereNotIn('id', $existingValueIds)->delete();

            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            $model->values()->delete();
            $model->translations()->delete();
            return $this->repository->delete($model->id);
        });
    }
}
