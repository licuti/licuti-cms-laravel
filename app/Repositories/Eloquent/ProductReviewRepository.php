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

    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['product_id']) && $filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->with(['product:id,name', 'user:id,name,email'])->latest()->paginate($perPage);
    }
}
