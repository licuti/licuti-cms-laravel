<?php

namespace App\Services\Admin\ProductReview;

use App\Core\Base\BaseService;
use App\Core\Enums\ReviewStatus;
use App\Events\ReviewApproved;
use App\Events\ReviewRejected;
use App\Models\ProductReview;
use App\Repositories\Interfaces\ProductReviewRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductReviewService extends BaseService
{
    public function __construct(
        private readonly ProductReviewRepositoryInterface $repository
    ) {}

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters);
    }

    public function approve(string $uuid): ProductReview
    {
        return DB::transaction(function () use ($uuid) {
            $review = $this->repository->lockForUpdateFindByUuid($uuid);

            if ($review->status === ReviewStatus::APPROVED) {
                return $review;
            }

            $this->repository->update($review->id, ['status' => ReviewStatus::APPROVED->value]);
            $review->refresh();
            ReviewApproved::dispatch($review);

            return $review;
        });
    }

    public function reject(string $uuid): ProductReview
    {
        $model = $this->repository->findByUuid($uuid);
        $this->repository->update($model->id, ['status' => ReviewStatus::REJECTED->value]);
        $model->refresh();
        event(new ReviewRejected($model));

        return $model;
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);

        return $this->repository->delete($model->id);
    }
}
