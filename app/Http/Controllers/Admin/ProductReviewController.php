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
        return view('admin.product-reviews.index', compact('reviews'));
    }

    public function approve(string $uuid): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $this->service->approve($uuid);
            if (request()->wantsJson()) {
                return $this->successResponse(message: __('Đã duyệt đánh giá.'));
            }
            return redirect()->back()->with('success', __('Đã duyệt đánh giá.'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(string $uuid): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $this->service->reject($uuid);
            if (request()->wantsJson()) {
                return $this->successResponse(message: __('Đã từ chối đánh giá.'));
            }
            return redirect()->back()->with('success', __('Đã từ chối đánh giá.'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $uuid): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $this->service->delete($uuid);
            if (request()->wantsJson()) {
                return $this->successResponse(message: __('Xóa thành công.'));
            }
            return redirect()->back()->with('success', __('Xóa thành công.'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
