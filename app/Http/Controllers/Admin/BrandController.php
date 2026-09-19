<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Brand\BrandDTO;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Services\Admin\Brand\BrandService;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends BaseController
{
    public function __construct(
        private readonly BrandService $service,
        private readonly BrandRepositoryInterface $repository,
        private readonly \App\Repositories\Interfaces\LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function index(Request $request): View
    {
        $brands = $this->service->getList($request->all());
        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.form', $this->formViewData());
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request);
        $brand = $this->service->create($dto);
        
        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.brands.edit', $brand->uuid)
                ->with('success', __('Thêm mới thương hiệu thành công.'));
        }

        return redirect()->route('admin.brands.index')
            ->with('success', __('Thêm mới thương hiệu thành công.'));
    }

    public function edit(string $uuid): View
    {
        $brand = $this->repository->findByUuidWithRelations($uuid);
        return view('admin.brands.form', $this->formViewData($brand));
    }

    public function update(UpdateBrandRequest $request, string $uuid): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.brands.index')
            ->with('success', __('Cập nhật thương hiệu thành công.'));
    }

    public function destroy(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa thương hiệu thành công.'));
            }

            return redirect()->route('admin.brands.index')
                ->with('success', __('Xóa thương hiệu thành công.'));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->errorResponse(message: $e->getMessage(), code: 400);
            }

            return redirect()->route('admin.brands.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Dữ liệu dùng chung cho create/edit form thương hiệu.
     */
    private function formViewData(?\App\Models\Brand $brand = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return [
            'brand'           => $brand,
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }
}
