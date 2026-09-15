<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\PaymentMethod\PaymentMethodDTO;
use App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest;
use App\Services\Admin\PaymentMethod\PaymentMethodService;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentMethodController extends BaseController
{
    public function __construct(
        private readonly PaymentMethodService $service,
        private readonly PaymentMethodRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $PaymentMethods = $this->service->getList(request()->all());
        return view('admin.payment-methods.index', compact('PaymentMethods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.form');
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $dto = PaymentMethodDTO::fromRequest($request);
        $this->service->create($dto);
        
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
        $dto = PaymentMethodDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.payment-methods.index')
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
