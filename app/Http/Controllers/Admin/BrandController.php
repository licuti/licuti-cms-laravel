<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Brand\BrandDTO;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Services\Admin\Brand\BrandService;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BrandController extends BaseController
{
    public function __construct(
        private readonly BrandService $service,
        private readonly BrandRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Brands = $this->service->getList(request()->all());
        return view('admin.brands.index', compact('Brands'));
    }

    public function create(): View
    {
        return view('admin.brands.form');
    }

    public function store(StoreBrandRequest $request)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.brands.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $brand = $this->repository->findByUuidOrFail($uuid);
        return view('admin.brands.form', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, string $uuid)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.brands.index')
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
