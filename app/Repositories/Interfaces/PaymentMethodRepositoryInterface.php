<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PaymentMethod;

interface PaymentMethodRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCodes(): array;
}
