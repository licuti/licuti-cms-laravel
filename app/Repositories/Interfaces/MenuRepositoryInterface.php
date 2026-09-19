<?php

namespace App\Repositories\Interfaces;

use App\Models\Menu;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Menu;

    public function getWithItems(string $uuid): ?Menu;

    public function getByLocation(string $location): ?Menu;
}
