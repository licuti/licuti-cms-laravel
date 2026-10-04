<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Http\Requests\Admin\ProductAttribute\StoreProductAttributeValueRequest;
use App\Http\Requests\Admin\ProductAttribute\UpdateProductAttributeValueRequest;
use App\Services\Admin\ProductAttribute\ProductAttributeValueService;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeValueRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\DTOs\ProductAttribute\ProductAttributeValueDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductAttributeValueController extends BaseController
{
    public function __construct(
        private readonly ProductAttributeValueService $service,
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly ProductAttributeValueRepositoryInterface $valueRepository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function index(Request $request, string $attributeUuid): \Illuminate\View\View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $values = $this->service->getList($attribute->id, $request->all());

        return view('admin.product-attribute-values.index', compact('attribute', 'values'));
    }

    public function create(string $attributeUuid): \Illuminate\View\View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.product-attribute-values.form', compact('attribute', 'activeLanguages', 'defaultLocale'));
    }

    public function store(StoreProductAttributeValueRequest $request, string $attributeUuid): \Illuminate\Http\RedirectResponse
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $dto = ProductAttributeValueDTO::fromRequest($request, $attribute->id);
        
        $valueModel = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.product-attributes.values.edit', [$attributeUuid, $valueModel->uuid])
                ->with('success', __('Lưu giá trị thành công.'));
        }

        return redirect()->route('admin.product-attributes.values.index', $attributeUuid)
            ->with('success', __('Thêm giá trị thuộc tính thành công.'));
    }

    public function edit(string $attributeUuid, string $uuid): \Illuminate\View\View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $valueModel = $this->valueRepository->findByUuidWithRelations($uuid);

        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.product-attribute-values.form', [
            'attribute' => $attribute,
            'valueModel' => $valueModel,
            'activeLanguages' => $activeLanguages,
            'defaultLocale' => $defaultLocale
        ]);
    }

    public function update(UpdateProductAttributeValueRequest $request, string $attributeUuid, string $uuid): \Illuminate\Http\RedirectResponse
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $dto = ProductAttributeValueDTO::fromRequest($request, $attribute->id);

        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.product-attributes.values.edit', [$attributeUuid, $uuid])
                ->with('success', __('Cập nhật giá trị thành công.'));
        }

        return redirect()->route('admin.product-attributes.values.index', $attributeUuid)
            ->with('success', __('Cập nhật giá trị thuộc tính thành công.'));
    }

    public function destroy(Request $request, string $attributeUuid, string $uuid): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $this->service->delete($uuid);
            
            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa giá trị thuộc tính thành công.'));
            }
            
            return redirect()->back()
                ->with('success', __('Xóa giá trị thuộc tính thành công.'));
        } catch (\Exception $e) {
            Log::error('Error deleting product attribute value', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
