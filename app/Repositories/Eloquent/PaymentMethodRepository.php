<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getActiveCodes(): array
    {
        return $this->model->where('is_active', true)->pluck('code')->toArray();
    }
}
