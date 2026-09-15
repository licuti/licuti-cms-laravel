<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Coupon\CouponDTO;
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
        $Coupons = $this->service->getList(request()->all());
        return view('admin.coupons.index', compact('Coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.form');
    }

    public function store(StoreCouponRequest $request)
    {
        $dto = CouponDTO::fromRequest($request);
        $this->service->create($dto);
        
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
        $dto = CouponDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.coupons.index')
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
