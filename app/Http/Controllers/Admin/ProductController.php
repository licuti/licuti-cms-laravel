<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Product\ProductDTO;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Services\Admin\Product\ProductService;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends BaseController
{
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Products = $this->service->getList(request()->all());
        return view('admin.products.index', compact('Products'));
    }

    public function create(): View
    {
        return view('admin.products.form');
    }

    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.products.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $product = $this->repository->findByUuidOrFail($uuid);
        return view('admin.products.form', compact('product'));
    }

    public function update(UpdateProductRequest $request, string $uuid)
    {
        $dto = ProductDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.products.index')
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
