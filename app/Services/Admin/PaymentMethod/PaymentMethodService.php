<?php

namespace App\Services\Admin\PaymentMethod;

use App\Core\Base\BaseService;
use App\Models\PaymentMethod;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentMethodService extends BaseService
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters);
    }

    public function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            $data['uuid'] = Str::uuid()->toString();
            $data['is_active'] = $data['is_active'] ?? true;
            $data['display_order'] = $data['display_order'] ?? 0;

            if (isset($data['config']) && is_string($data['config'])) {
                $decoded = json_decode($data['config'], true);
                $data['config'] = is_array($decoded) ? $decoded : null;
            }

            $model = $this->repository->create($data);
            if (method_exists($this->repository, 'clearCache')) {
                $this->repository->clearCache();
            }

            return $model;
        });
    }

    public function update(string $uuid, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($uuid, $data) {
            $model = $this->repository->findByUuid($uuid);

            $data['is_active'] = $data['is_active'] ?? true;
            if (isset($data['config']) && is_string($data['config'])) {
                $decoded = json_decode($data['config'], true);
                $data['config'] = is_array($decoded) ? $decoded : null;
            }

            $this->repository->update($model->id, $data);

            if (method_exists($this->repository, 'clearCache')) {
                $this->repository->clearCache();
            }

            return $model->fresh();
        });
    }

    public function toggleStatus(string $uuid): PaymentMethod
    {
        $model = $this->repository->findByUuid($uuid);
        $this->repository->update($model->id, ['is_active' => !$model->is_active]);

        if (method_exists($this->repository, 'clearCache')) {
            $this->repository->clearCache();
        }

        return $model->refresh();
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        $deleted = $this->repository->delete($model->id);

        if (method_exists($this->repository, 'clearCache')) {
            $this->repository->clearCache();
        }

        return $deleted;
    }

    public function reorder(array $orderedIds): void
    {
        $this->repository->reorder($orderedIds);
    }
}
