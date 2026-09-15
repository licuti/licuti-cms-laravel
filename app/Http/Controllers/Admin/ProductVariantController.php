<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\ProductVariant\ProductVariantDTO;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Services\Admin\ProductVariant\ProductVariantService;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductVariantController extends BaseController
{
    public function __construct(
        private readonly ProductVariantService $service,
        private readonly ProductVariantRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $ProductVariants = $this->service->getList(request()->all());
        return view('admin.product-variants.index', compact('ProductVariants'));
    }

    public function create(): View
    {
        return view('admin.product-variants.form');
    }

    public function store(StoreProductVariantRequest $request)
    {
        $dto = ProductVariantDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.product-variants.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $productVariant = $this->repository->findByUuidOrFail($uuid);
        return view('admin.product-variants.form', compact('productVariant'));
    }

    public function update(UpdateProductVariantRequest $request, string $uuid)
    {
        $dto = ProductVariantDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.product-variants.index')
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
