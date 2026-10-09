<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest;
use App\Services\Admin\PaymentMethod\PaymentMethodService;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public function __construct(
        private readonly PaymentMethodService $service,
        private readonly PaymentMethodRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $paymentMethods = $this->service->getList(request()->all());
        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.form');
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $this->service->create($request->validated());
        
        return redirect()->route('admin.payment-methods.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $paymentMethod = $this->repository->findByUuidOrFail($uuid);
        return view('admin.payment-methods.form', compact('paymentMethod'));
    }

    public function update(UpdatePaymentMethodRequest $request, string $uuid)
    {
        $this->service->update($uuid, $request->validated());
        
        return redirect()->route('admin.payment-methods.index')
            ->with('success', __('Cập nhật thành công.'));
    }

    public function toggleStatus(string $uuid)
    {
        try {
            $this->service->toggleStatus($uuid);
            return back()->with('success', __('Cập nhật trạng thái thành công.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
