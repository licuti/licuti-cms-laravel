<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Order;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator;
}
