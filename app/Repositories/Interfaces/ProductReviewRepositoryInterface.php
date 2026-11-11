<?php

namespace App\Repositories\Interfaces;

use App\Models\ProductReview;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductReviewRepositoryInterface extends BaseRepositoryInterface
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function lockForUpdateFindByUuid(string $uuid): ProductReview;
}
