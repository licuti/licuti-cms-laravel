<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Warehouse\WarehouseDTO;
use App\Http\Requests\Admin\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Admin\Warehouse\UpdateWarehouseRequest;
use App\Services\Admin\Warehouse\WarehouseService;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WarehouseController extends BaseController
{
    public function __construct(
        private readonly WarehouseService $service,
        private readonly WarehouseRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Warehouses = $this->service->getList(request()->all());
        return view('admin.warehouses.index', compact('Warehouses'));
    }

    public function create(): View
    {
        return view('admin.warehouses.form');
    }

    public function store(StoreWarehouseRequest $request)
    {
        $dto = WarehouseDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.warehouses.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $warehouse = $this->repository->findByUuidOrFail($uuid);
        return view('admin.warehouses.form', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, string $uuid)
    {
        $dto = WarehouseDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.warehouses.index')
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
