<?php

namespace App\Repositories\Interfaces;

use App\Models\Banner;
use Illuminate\Pagination\LengthAwarePaginator;

interface BannerRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuidWithRelations(string $uuid): ?Banner;

    public function getActiveByPosition(string $position);
}
