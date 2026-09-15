<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\FlashSale;

class FlashSaleRepository extends BaseRepository implements FlashSaleRepositoryInterface
{
    public function __construct(FlashSale $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }
}
