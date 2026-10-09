<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Services\Admin\ProductReview\ProductReviewService;
use App\Repositories\Interfaces\ProductReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductReviewController extends BaseController
{
    public function __construct(
        private readonly ProductReviewService $service,
        private readonly ProductReviewRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $reviews = $this->service->getList(request()->all());
        $reviews->load(['product:id,name', 'user:id,name,email']);
        return view('admin.product-reviews.index', compact('reviews'));
    }

    public function approve(string $uuid)
    {
        try {
            $this->service->approve($uuid);
            return $this->successResponse(message: __('Đã duyệt đánh giá.'));
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), code: 400);
        }
    }

    public function reject(string $uuid)
    {
        try {
            $this->service->reject($uuid);
            return $this->successResponse(message: __('Đã từ chối đánh giá.'));
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), code: 400);
        }
    }

    public function destroy(string $uuid)
    {
        try {
            $this->service->delete($uuid);
            return $this->successResponse(message: __('Xóa thành công.'));
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), code: 400);
        }
    }
}
