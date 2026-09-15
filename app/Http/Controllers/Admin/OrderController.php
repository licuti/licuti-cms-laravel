<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Order\OrderDTO;
use App\Http\Requests\Admin\Order\StoreOrderRequest;
use App\Http\Requests\Admin\Order\UpdateOrderRequest;
use App\Services\Admin\Order\OrderService;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderController extends BaseController
{
    public function __construct(
        private readonly OrderService $service,
        private readonly OrderRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Orders = $this->service->getList(request()->all());
        return view('admin.orders.index', compact('Orders'));
    }

    public function create(): View
    {
        return view('admin.orders.form');
    }

    public function store(StoreOrderRequest $request)
    {
        $dto = OrderDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.orders.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $order = $this->repository->findByUuidOrFail($uuid);
        return view('admin.orders.form', compact('order'));
    }

    public function update(UpdateOrderRequest $request, string $uuid)
    {
        $dto = OrderDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.orders.index')
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
