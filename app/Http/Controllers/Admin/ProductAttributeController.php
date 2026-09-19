<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\ProductAttribute\ProductAttributeDTO;
use App\Http\Requests\Admin\ProductAttribute\StoreProductAttributeRequest;
use App\Http\Requests\Admin\ProductAttribute\UpdateProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductAttributeController extends BaseController
{
    public function __construct(
        private readonly ProductAttributeService $service,
        private readonly ProductAttributeRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function index(Request $request): View
    {
        $attributes = $this->service->getList($request->all());
        $types = [
            'select' => __('Hộp chọn (Select)'),
            'color'  => __('Màu sắc (Color Swatch)'),
            'button' => __('Nút bấm (Button/Text)'),
            'radio'  => __('Nút chọn đơn (Radio)'),
        ];

        return view('admin.product-attributes.index', compact('attributes', 'types'));
    }

    public function create(): View
    {
        return view('admin.product-attributes.form', $this->formViewData());
    }

    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $dto = ProductAttributeDTO::fromRequest($request);
        $attribute = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.product-attributes.edit', $attribute->uuid)
                ->with('success', __('Thêm thuộc tính sản phẩm thành công.'));
        }

        return redirect()->route('admin.product-attributes.index')
            ->with('success', __('Thêm thuộc tính sản phẩm thành công.'));
    }

    public function edit(string $uuid): View
    {
        $attribute = $this->repository->findByUuidWithRelations($uuid);
        return view('admin.product-attributes.form', $this->formViewData($attribute));
    }

    public function update(UpdateProductAttributeRequest $request, string $uuid): RedirectResponse
    {
        $dto = ProductAttributeDTO::fromRequest($request);
        $attribute = $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.product-attributes.edit', $attribute->uuid)
                ->with('success', __('Cập nhật thuộc tính sản phẩm thành công.'));
        }

        return redirect()->route('admin.product-attributes.index')
            ->with('success', __('Cập nhật thuộc tính sản phẩm thành công.'));
    }

    public function destroy(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa thuộc tính sản phẩm thành công.'));
            }

            return redirect()->route('admin.product-attributes.index')
                ->with('success', __('Xóa thuộc tính sản phẩm thành công.'));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }

            return redirect()->route('admin.product-attributes.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Dữ liệu dùng chung cho form create/edit thuộc tính.
     */
    private function formViewData(?ProductAttribute $attribute = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return [
            'attribute'       => $attribute,
            'types'           => [
                'select' => __('Hộp chọn (Select)'),
                'color'  => __('Màu sắc (Color Swatch)'),
                'button' => __('Nút bấm (Button/Text)'),
                'radio'  => __('Nút chọn đơn (Radio)'),
            ],
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }
}
