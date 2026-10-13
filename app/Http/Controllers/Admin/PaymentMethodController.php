<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Payment\PaymentGatewayRegistry;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest;
use App\Services\Admin\PaymentMethod\PaymentMethodService;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public function __construct(
        private readonly PaymentMethodService $service,
        private readonly PaymentMethodRepositoryInterface $repository
    ) {
    }

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        $paymentMethods = $this->service->getList(request()->all());
        $bulkActions = $bulkRegistry->getActionOptions('payment_methods');
        return view('admin.payment-methods.index', compact('paymentMethods', 'bulkActions'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'payment_methods',
            $request->input('action'),
            $request->input('ids')
        );

        return back()->with('success', __('Thao tác hàng loạt thành công.'));
    }

    public function create(PaymentGatewayRegistry $gatewayRegistry): View
    {
        $gatewayOptions = $gatewayRegistry->getOptions();
        $gatewaySchemas = $gatewayRegistry->getAllConfigSchemas();
        return view('admin.payment-methods.form', compact('gatewayOptions', 'gatewaySchemas'));
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $paymentMethod = $this->service->create($request->validated());
        
        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.payment-methods.edit', $paymentMethod->uuid)
                ->with('success', __('Thêm mới thành công.'));
        }

        return redirect()->route('admin.payment-methods.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid, PaymentGatewayRegistry $gatewayRegistry): View
    {
        $paymentMethod = $this->repository->findByUuidOrFail($uuid);
        $gatewayOptions = $gatewayRegistry->getOptions();
        $gatewaySchemas = $gatewayRegistry->getAllConfigSchemas();
        return view('admin.payment-methods.form', compact('paymentMethod', 'gatewayOptions', 'gatewaySchemas'));
    }

    public function update(UpdatePaymentMethodRequest $request, string $uuid)
    {
        $this->service->update($uuid, $request->validated());
        
        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.payment-methods.edit', $uuid)
                ->with('success', __('Cập nhật thành công.'));
        }

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
