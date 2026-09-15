<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = []): mixed
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): mixed
    {
        return $this->model->with($relations)
            ->where('uuid', $uuid)
            ->firstOrFail($columns);
    }

    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        return $this->model->where($criteria)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): mixed
    {
        $record = $this->find($id);
        $record->update($data);

        return $record->fresh();
    }

    public function updateOrCreate(array $attributes, array $values = []): mixed
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    public function findMany(array $ids, array $columns = ['*']): Collection
    {
        return $this->model->findMany($ids, $columns);
    }

    public function findManyByUuids(array $uuids, array $columns = ['*']): Collection
    {
        return $this->model->whereIn('uuid', $uuids)->get($columns);
    }

    public function updateByUuids(array $uuids, array $data): int
    {
        return $this->model->whereIn('uuid', $uuids)->update($data);
    }

    public function count(array $criteria = []): int
    {
        return $this->model->where($criteria)->count();
    }
}