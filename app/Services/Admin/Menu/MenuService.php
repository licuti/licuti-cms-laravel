<?php

namespace App\Services\Admin\Menu;

use App\Core\Base\BaseService;
use App\DTOs\Menu\MenuDTO;
use App\Models\Menu;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MenuService extends BaseService
{
    public function __construct(
        private readonly MenuRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated();
    }

    public function create(MenuDTO $dto): Menu
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            return $model;
        });
    }

    public function update(string $uuid, MenuDTO $dto): Menu
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
