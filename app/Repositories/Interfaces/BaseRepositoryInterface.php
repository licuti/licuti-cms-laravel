<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function find(int $id, array $columns = ['*'], array $relations = []): mixed;

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): mixed;

    public function findWhere(array $criteria, array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    public function create(array $data): mixed;

    public function update(int $id, array $data): mixed;

    public function updateOrCreate(array $attributes, array $values = []): mixed;

    public function delete(int $id): bool;

    public function findMany(array $ids, array $columns = ['*']): Collection;

    public function findManyByUuids(array $uuids, array $columns = ['*']): Collection;

    public function updateByUuids(array $uuids, array $data): int;

    public function count(array $criteria = []): int;
}