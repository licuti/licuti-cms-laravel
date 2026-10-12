<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\Http\Requests\Admin\BulkActionRequest;
use App\DTOs\Coupon\CouponDTO;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Services\Admin\Coupon\CouponService;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends BaseController
{
    public function __construct(
        private readonly CouponService $service,
        private readonly CouponRepositoryInterface $repository
    ) {
    }

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        $coupons = $this->service->getList(request()->all());
        $bulkActions = $bulkRegistry->getActionOptions('coupons');
        return view('admin.coupons.index', compact('coupons', 'bulkActions'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'coupons',
            $request->input('action'),
            $request->input('ids')
        );

        return back()->with('success', __('Thao tác hàng loạt thành công.'));
    }

    public function create(): View
    {
        return view('admin.coupons.form');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $dto = CouponDTO::fromRequest($request);
        $coupon = $this->service->create($dto);
        
        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.coupons.edit', $coupon->uuid)
                ->with('success', __('Thêm mới thành công.'));
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $coupon = $this->repository->findByUuidOrFail($uuid);
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, string $uuid): RedirectResponse
    {
        $dto = CouponDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.coupons.edit', $uuid)
                ->with('success', __('Cập nhật thành công.'));
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', __('Cập nhật thành công.'));
    }

    public function toggleStatus(string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $coupon = clone $this->repository->findByUuidOrFail($uuid);
            $coupon->is_active = !$coupon->is_active;
            $this->repository->update($coupon->id, ['is_active' => $coupon->is_active]);
            
            if (request()->wantsJson()) {
                return $this->successResponse(
                    message: __('Cập nhật trạng thái thành công.'),
                    data: ['is_active' => $coupon->is_active]
                );
            }
            return back()->with('success', __('Cập nhật trạng thái thành công.'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);
            if (request()->wantsJson()) {
                return $this->successResponse(message: __('Xóa thành công.'));
            }
            return redirect()->route('admin.coupons.index')
                ->with('success', __('Xóa thành công.'));
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }
            return redirect()->route('admin.coupons.index')
                ->with('error', $e->getMessage());
        }
    }
}
