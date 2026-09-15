<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\ProductReview\ProductReviewDTO;
use App\Http\Requests\Admin\ProductReview\StoreProductReviewRequest;
use App\Http\Requests\Admin\ProductReview\UpdateProductReviewRequest;
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
        $ProductReviews = $this->service->getList(request()->all());
        return view('admin.product-reviews.index', compact('ProductReviews'));
    }

    public function create(): View
    {
        return view('admin.product-reviews.form');
    }

    public function store(StoreProductReviewRequest $request)
    {
        $dto = ProductReviewDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.product-reviews.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $productReview = $this->repository->findByUuidOrFail($uuid);
        return view('admin.product-reviews.form', compact('productReview'));
    }

    public function update(UpdateProductReviewRequest $request, string $uuid)
    {
        $dto = ProductReviewDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.product-reviews.index')
            ->with('success', __('Cập nhật thành công.'));
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
