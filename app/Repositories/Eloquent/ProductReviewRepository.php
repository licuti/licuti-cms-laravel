<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductReviewRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ProductReview;

class ProductReviewRepository extends BaseRepository implements ProductReviewRepositoryInterface
{
    public function __construct(ProductReview $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }
}
