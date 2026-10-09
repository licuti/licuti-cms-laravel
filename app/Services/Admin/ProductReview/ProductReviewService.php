<?php

namespace App\Services\Admin\ProductReview;

use App\Core\Base\BaseService;
use App\Models\ProductReview;
use App\Repositories\Interfaces\ProductReviewRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductReviewService extends BaseService
{
    public function __construct(
        private readonly ProductReviewRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters);
    }

    public function approve(string $uuid): ProductReview
    {
        $model = $this->repository->findByUuid($uuid);
        $this->repository->update($model->id, ['status' => \App\Core\Enums\ReviewStatus::APPROVED->value]);
        $model->refresh();
        event(new \App\Events\ReviewApproved($model));
        return $model;
    }

    public function reject(string $uuid): ProductReview
    {
        $model = $this->repository->findByUuid($uuid);
        $this->repository->update($model->id, ['status' => \App\Core\Enums\ReviewStatus::REJECTED->value]);
        $model->refresh();
        event(new \App\Events\ReviewRejected($model));
        return $model;
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
