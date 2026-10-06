<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\ProductAttribute\ProductAttributeValueDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\ProductAttribute\MoveProductAttributeValueRequest;
use App\Http\Requests\Admin\ProductAttribute\StoreProductAttributeValueRequest;
use App\Http\Requests\Admin\ProductAttribute\UpdateProductAttributeValueRequest;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeValueRepositoryInterface;
use App\Services\Admin\ProductAttribute\ProductAttributeValueService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductAttributeValueController extends BaseController
{
    public function __construct(
        private readonly ProductAttributeValueService $service,
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly ProductAttributeValueRepositoryInterface $valueRepository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(Request $request, BulkActionRegistry $bulkRegistry, string $attributeUuid): View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $values = $this->service->getList($attribute->id, $request->all());
        $bulkActions = $bulkRegistry->getActionOptions('product_attribute_values');

        return view('admin.product-attribute-values.index', compact('attribute', 'values', 'bulkActions'));
    }

    public function create(string $attributeUuid): View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.product-attribute-values.form', compact('attribute', 'activeLanguages', 'defaultLocale'));
    }

    public function store(StoreProductAttributeValueRequest $request, string $attributeUuid): RedirectResponse
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

    public function edit(string $attributeUuid, string $uuid): View
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        $valueModel = $this->valueRepository->findByUuidAndAttribute($uuid, $attribute->id);

        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLocale = $activeLanguages->firstWhere('is_default', true)?->code ?? app()->getLocale();

        return view('admin.product-attribute-values.form', [
            'attribute' => $attribute,
            'valueModel' => $valueModel,
            'activeLanguages' => $activeLanguages,
            'defaultLocale' => $defaultLocale,
        ]);
    }

    public function update(UpdateProductAttributeValueRequest $request, string $attributeUuid, string $uuid): RedirectResponse
    {
        $attribute = $this->attributeRepository->findByUuid($attributeUuid);
        // Scope value theo attribute — cặp attribute/value sai trả 404 (chống IDOR).
        $this->valueRepository->findByUuidAndAttribute($uuid, $attribute->id);

        $dto = ProductAttributeValueDTO::fromRequest($request, $attribute->id);

        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.product-attributes.values.edit', [$attributeUuid, $uuid])
                ->with('success', __('Cập nhật giá trị thành công.'));
        }

        return redirect()->route('admin.product-attributes.values.index', $attributeUuid)
            ->with('success', __('Cập nhật giá trị thuộc tính thành công.'));
    }

    public function destroy(Request $request, string $attributeUuid, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            // Scope value theo attribute — cặp attribute/value sai trả 404 (chống IDOR).
            $attribute = $this->attributeRepository->findByUuid($attributeUuid);
            $this->valueRepository->findByUuidAndAttribute($uuid, $attribute->id);

            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa giá trị thuộc tính thành công.'));
            }

            return redirect()->back()
                ->with('success', __('Xóa giá trị thuộc tính thành công.'));
        } catch (\Exception $e) {
            // 404 (cặp attribute/value sai) phải lan truyền, không bị nuốt thành redirect.
            if ($e instanceof ModelNotFoundException || $e instanceof HttpException) {
                throw $e;
            }

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

    public function bulk(BulkActionRequest $request, string $attributeUuid): RedirectResponse
    {
        $registry = app(BulkActionRegistry::class);

        try {
            $registry->dispatch(
                'product_attribute_values',
                $request->input('action'),
                $request->input('ids')
            );
        } catch (\Exception $e) {
            return redirect()->route('admin.product-attributes.values.index', $attributeUuid)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.product-attributes.values.index', $attributeUuid)
            ->with('success', __('Thao tác hàng loạt thành công.'));
    }

    public function moveUp(MoveProductAttributeValueRequest $request, string $attributeUuid, string $uuid): RedirectResponse
    {
        $this->service->moveValue($attributeUuid, $uuid, 'up');

        return redirect()->back()
            ->with('success', __('Đã di chuyển giá trị lên.'));
    }

    public function moveDown(MoveProductAttributeValueRequest $request, string $attributeUuid, string $uuid): RedirectResponse
    {
        $this->service->moveValue($attributeUuid, $uuid, 'down');

        return redirect()->back()
            ->with('success', __('Đã di chuyển giá trị xuống.'));
    }
}
