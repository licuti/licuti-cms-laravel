<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\ProductAttribute\ProductAttributeDTO;
use App\Http\Requests\Admin\ProductAttribute\StoreProductAttributeRequest;
use App\Http\Requests\Admin\ProductAttribute\UpdateProductAttributeRequest;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductAttributeController extends BaseController
{
    public function __construct(
        private readonly ProductAttributeService $service,
        private readonly ProductAttributeRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $ProductAttributes = $this->service->getList(request()->all());
        return view('admin.product-attributes.index', compact('ProductAttributes'));
    }

    public function create(): View
    {
        return view('admin.product-attributes.form');
    }

    public function store(StoreProductAttributeRequest $request)
    {
        $dto = ProductAttributeDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.product-attributes.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $productAttribute = $this->repository->findByUuidOrFail($uuid);
        return view('admin.product-attributes.form', compact('productAttribute'));
    }

    public function update(UpdateProductAttributeRequest $request, string $uuid)
    {
        $dto = ProductAttributeDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.product-attributes.index')
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
