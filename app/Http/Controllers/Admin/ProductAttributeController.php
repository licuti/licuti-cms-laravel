<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\ProductAttribute\ProductAttributeDTO;
use App\Http\Requests\Admin\BulkActionRequest;
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
use Illuminate\Support\Facades\Log;

class ProductAttributeController extends BaseController
{
    public function __construct(
        private readonly ProductAttributeService $service,
        private readonly ProductAttributeRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function index(Request $request, BulkActionRegistry $bulkRegistry): View
    {
        $attributes = $this->service->getList($request->all());
        $bulkActions = $bulkRegistry->getActionOptions('product_attributes');

        return view('admin.product-attributes.index', array_merge(
            compact('attributes', 'bulkActions'),
            $this->getAttributeTypes()
        ));
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
            Log::error('Error deleting product attribute', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }

            return redirect()->route('admin.product-attributes.index')
                ->with('error', $e->getMessage());
        }
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'product_attributes',
            $request->input('action'),
            $request->input('ids')
        );

        return redirect()->route('admin.product-attributes.index')
            ->with('success', __('Thao tác hàng loạt thành công.'));
    }

    /**
     * Dữ liệu dùng chung cho form create/edit thuộc tính.
     */
    private function formViewData(?ProductAttribute $attribute = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return array_merge([
            'attribute'       => $attribute,
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ], $this->getAttributeTypes());
    }

    private function getAttributeTypes(): array
    {
        return [
            'types' => [
                \App\Enums\AttributeType::SELECT->value => \App\Enums\AttributeType::SELECT->label(),
                \App\Enums\AttributeType::COLOR->value  => \App\Enums\AttributeType::COLOR->label(),
                \App\Enums\AttributeType::BUTTON->value => \App\Enums\AttributeType::BUTTON->label(),
                \App\Enums\AttributeType::RADIO->value  => \App\Enums\AttributeType::RADIO->label(),
            ]
        ];
    }
}
