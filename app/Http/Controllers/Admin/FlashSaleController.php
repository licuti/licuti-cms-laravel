<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\FlashSale\FlashSaleDTO;
use App\Http\Requests\Admin\FlashSale\StoreFlashSaleRequest;
use App\Http\Requests\Admin\FlashSale\UpdateFlashSaleRequest;
use App\Services\Admin\FlashSale\FlashSaleService;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FlashSaleController extends BaseController
{
    public function __construct(
        private readonly FlashSaleService $service,
        private readonly FlashSaleRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $FlashSales = $this->service->getList(request()->all());
        return view('admin.flash-sales.index', compact('FlashSales'));
    }

    public function create(): View
    {
        return view('admin.flash-sales.form');
    }

    public function store(StoreFlashSaleRequest $request)
    {
        $dto = FlashSaleDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.flash-sales.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $flashSale = $this->repository->findByUuidOrFail($uuid);
        return view('admin.flash-sales.form', compact('flashSale'));
    }

    public function update(UpdateFlashSaleRequest $request, string $uuid)
    {
        $dto = FlashSaleDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.flash-sales.index')
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
