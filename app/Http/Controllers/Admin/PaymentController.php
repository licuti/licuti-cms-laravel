<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Payment\PaymentDTO;
use App\Http\Requests\Admin\Payment\StorePaymentRequest;
use App\Http\Requests\Admin\Payment\UpdatePaymentRequest;
use App\Services\Admin\Payment\PaymentService;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentController extends BaseController
{
    public function __construct(
        private readonly PaymentService $service,
        private readonly PaymentRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Payments = $this->service->getList(request()->all());
        return view('admin.payments.index', compact('Payments'));
    }

    public function create(): View
    {
        return view('admin.payments.form');
    }

    public function store(StorePaymentRequest $request)
    {
        $dto = PaymentDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.payments.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $payment = $this->repository->findByUuidOrFail($uuid);
        return view('admin.payments.form', compact('payment'));
    }

    public function update(UpdatePaymentRequest $request, string $uuid)
    {
        $dto = PaymentDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.payments.index')
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
