<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Payment;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator;
}
