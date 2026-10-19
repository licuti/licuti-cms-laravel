<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Http\Requests\Admin\Order\StoreOrderRequest;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Services\Admin\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class OrderController extends BaseController
{
    public function __construct(
        private readonly OrderService $service
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'search', 'payment_method_id', 'date_from', 'date_to']);
        $orders = $this->service->getList($filters);
        $statusCounts = $this->service->getStatusCounts();
        $paymentMethods = $this->service->getActivePaymentMethods();

        return view('admin.orders.index', compact('orders', 'statusCounts', 'paymentMethods'));
    }

    public function show(string $uuid): View
    {
        $order = $this->service->getDetail($uuid);
        $statuses = OrderStatus::cases();

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function create(): View
    {
        $paymentMethods = $this->service->getActivePaymentMethods();
        return view('admin.orders.form', compact('paymentMethods'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $dto = OrderDTO::fromRequest($request);
        $order = $this->service->create($dto);

        return redirect()->route('admin.orders.show', $order->uuid)
            ->with('success', __('Tạo đơn hàng #:number thành công.', ['number' => $order->order_number]));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $uuid): RedirectResponse
    {
        try {
            $status = OrderStatus::from($request->input('status'));
            $this->service->updateStatus(
                uuid: $uuid,
                newStatus: $status,
                note: $request->input('note'),
                changedBy: auth()->id()
            );

            return redirect()->route('admin.orders.show', $uuid)
                ->with('success', __('Đã chuyển trạng thái đơn hàng sang: :status.', ['status' => $status->label()]));
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.orders.show', $uuid)
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, string $uuid): RedirectResponse
    {
        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->service->cancel(
                uuid: $uuid,
                reason: $request->input('cancel_reason'),
                changedBy: auth()->id()
            );

            return redirect()->route('admin.orders.show', $uuid)
                ->with('success', __('Đã hủy đơn hàng thành công.'));
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.orders.show', $uuid)
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->delete($uuid);
            return $this->successResponse(message: __('Xóa đơn hàng thành công.'));
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), code: 400);
        }
    }
}
