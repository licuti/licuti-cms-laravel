<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Services\Admin\Coupon\CouponService;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CouponController extends BaseController
{
    public function __construct(
        private readonly CouponService $service,
        private readonly CouponRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $coupons = $this->service->getList(request()->all());
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.form');
    }

    public function store(StoreCouponRequest $request)
    {
        $this->service->create($request->validated());
        
        return redirect()->route('admin.coupons.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $coupon = $this->repository->findByUuidOrFail($uuid);
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, string $uuid)
    {
        $this->service->update($uuid, $request->validated());
        
        return redirect()->route('admin.coupons.index')
            ->with('success', __('Cập nhật thành công.'));
    }

    public function toggleStatus(string $uuid): JsonResponse
    {
        try {
            $coupon = clone $this->repository->findByUuidOrFail($uuid);
            $coupon->is_active = !$coupon->is_active;
            $this->repository->update($coupon->id, ['is_active' => $coupon->is_active]);
            
            return $this->successResponse(
                message: __('Cập nhật trạng thái thành công.'),
                data: ['is_active' => $coupon->is_active]
            );
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
