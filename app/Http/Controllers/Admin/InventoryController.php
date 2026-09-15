<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Inventory\InventoryDTO;
use App\Http\Requests\Admin\Inventory\StoreInventoryRequest;
use App\Http\Requests\Admin\Inventory\UpdateInventoryRequest;
use App\Services\Admin\Inventory\InventoryService;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InventoryController extends BaseController
{
    public function __construct(
        private readonly InventoryService $service,
        private readonly InventoryRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Inventories = $this->service->getList(request()->all());
        return view('admin.inventories.index', compact('Inventories'));
    }

    public function create(): View
    {
        return view('admin.inventories.form');
    }

    public function store(StoreInventoryRequest $request)
    {
        $dto = InventoryDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.inventories.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $inventory = $this->repository->findByUuidOrFail($uuid);
        return view('admin.inventories.form', compact('inventory'));
    }

    public function update(UpdateInventoryRequest $request, string $uuid)
    {
        $dto = InventoryDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.inventories.index')
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
