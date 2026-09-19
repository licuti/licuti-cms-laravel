<?php

namespace App\Services\Admin\Menu;

use App\Core\Base\BaseService;
use App\DTOs\Menu\MenuDTO;
use App\Models\Menu;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuService extends BaseService
{
    public function __construct(
        private readonly MenuRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    public function create(MenuDTO $dto): Menu
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);

            foreach ($dto->items as $index => $itemData) {
                if (!empty($itemData['title'])) {
                    $model->items()->create([
                        'uuid'          => Str::uuid()->toString(),
                        'title'         => $itemData['title'],
                        'url'           => $itemData['url'] ?? '/',
                        'target'        => $itemData['target'] ?? '_self',
                        'display_order' => $itemData['display_order'] ?? $index,
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, MenuDTO $dto): Menu
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            $submittedItems = $dto->items;
            $existingItemIds = [];

            foreach ($submittedItems as $index => $itemData) {
                if (empty($itemData['title'])) {
                    continue;
                }

                if (!empty($itemData['uuid'])) {
                    $existingItem = $model->items()->where('uuid', $itemData['uuid'])->first();
                    if ($existingItem) {
                        $existingItem->update([
                            'title'         => $itemData['title'],
                            'url'           => $itemData['url'] ?? '/',
                            'target'        => $itemData['target'] ?? '_self',
                            'display_order' => $itemData['display_order'] ?? $index,
                        ]);
                        $existingItemIds[] = $existingItem->id;
                        continue;
                    }
                }

                $newItem = $model->items()->create([
                    'uuid'          => Str::uuid()->toString(),
                    'title'         => $itemData['title'],
                    'url'           => $itemData['url'] ?? '/',
                    'target'        => $itemData['target'] ?? '_self',
                    'display_order' => $itemData['display_order'] ?? $index,
                ]);
                $existingItemIds[] = $newItem->id;
            }

            $model->items()->whereNotIn('id', $existingItemIds)->delete();

            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            $model->items()->delete();
            return $this->repository->delete($model->id);
        });
    }
}
