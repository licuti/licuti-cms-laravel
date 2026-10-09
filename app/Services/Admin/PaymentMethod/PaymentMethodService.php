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
        return $this->repository->paginate();
    }

    public function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            $data['uuid'] = Str::uuid()->toString();
            $data['is_active'] = $data['is_active'] ?? true;
            $data['config'] = isset($data['config']) ? json_decode($data['config'], true) : null;

            return $this->repository->create($data);
        });
    }

    public function update(string $uuid, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($uuid, $data) {
            $model = $this->repository->findByUuid($uuid);
            
            $data['is_active'] = $data['is_active'] ?? true;
            if (isset($data['config']) && is_string($data['config'])) {
                $data['config'] = json_decode($data['config'], true);
            }

            $this->repository->update($model->id, $data);
            return $model;
        });
    }

    public function toggleStatus(string $uuid): PaymentMethod
    {
        $model = $this->repository->findByUuid($uuid);
        $this->repository->update($model->id, ['is_active' => !$model->is_active]);
        return $model->refresh();
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
