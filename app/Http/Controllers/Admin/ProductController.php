<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Enums\ContentStatus;
use App\Core\Enums\ProductType;
use App\DTOs\Product\ProductDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\Product\StoreCustomAttributeRequest;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Admin\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends BaseController
{
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductRepositoryInterface $repository,
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly BrandRepositoryInterface $brandRepository,
        private readonly TagRepositoryInterface $tagRepository
    ) {}

    public function index(Request $request, BulkActionRegistry $bulkRegistry): View
    {
        $products = $this->service->getList($request->all());
        $categories = $this->categoryRepository->getForSelect();
        $brands = $this->brandRepository->getForSelect();
        $statuses = $this->statuses();
        $tabs = $this->service->getTabs();
        $bulkActions = $bulkRegistry->getActionOptions('products');
        $tab = $request->tab ?? 'all';

        return view('admin.products.index', compact('products', 'categories', 'brands', 'statuses', 'tabs', 'bulkActions', 'tab'));
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formViewData());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.products.edit', $product->uuid)
                ->with('success', __('Thêm sản phẩm mới thành công.'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('Thêm sản phẩm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $product = $this->repository->findByUuidWithRelations($uuid);

        return view('admin.products.form', $this->formViewData($product));
    }

    public function update(UpdateProductRequest $request, string $uuid): RedirectResponse
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.products.edit', $product->uuid)
                ->with('success', __('Cập nhật sản phẩm thành công.'));
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('Cập nhật sản phẩm thành công.'));
    }

    public function destroy(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa sản phẩm thành công.'));
            }

            return redirect()->route('admin.products.index')
                ->with('success', __('Xóa sản phẩm thành công.'));
        } catch (\Exception $e) {
            report($e);

            $message = __('Có lỗi xảy ra khi xóa sản phẩm. Vui lòng thử lại.');

            if ($request->wantsJson()) {
                return $this->errorResponse($message);
            }

            return redirect()->route('admin.products.index')
                ->with('error', $message);
        }
    }

    /**
     * Tạo thuộc tính custom (chỉ thuộc về product này) từ form sản phẩm.
     */
    public function storeAttribute(StoreCustomAttributeRequest $request, string $uuid): JsonResponse|RedirectResponse
    {
        $attribute = $this->service->createCustomAttribute($uuid, [
            'code' => $request->input('code'),
            'type' => $request->input('type', 'select'),
            'translations' => $request->input('translations', []),
            'values' => $request->input('values', []),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Đã thêm thuộc tính tùy chỉnh.'),
                'attribute' => [
                    'id' => $attribute->id,
                    'uuid' => $attribute->uuid,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'values' => $attribute->values->map(fn ($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'color_code' => $v->color_code,
                    ]),
                ],
            ]);
        }

        return redirect()->back()->with('success', __('Đã thêm thuộc tính tùy chỉnh.'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'products',
            $request->input('action'),
            $request->input('ids')
        );

        return redirect()->route('admin.products.index')
            ->with('success', __('Thao tác hàng loạt thành công.'));
    }

    private function statuses(): array
    {
        return collect(ContentStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->all();
    }

    private function formViewData(?Product $product = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale = $defaultLanguage?->code ?? app()->getLocale();

        $productId = $product?->id;

        $catalogAttributes = $productId
            ? $this->attributeRepository->getAvailableForProduct($productId)
            : $this->attributeRepository->getActiveWithValues();

        return [
            'product' => $product,
            'categories' => $this->categoryRepository->getForSelect(),
            'brands' => $this->brandRepository->getForSelect(),
            'tags' => $this->tagRepository->getActiveOrdered(),
            'productTypes' => $this->productTypes(),
            'statuses' => $this->statuses(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale' => $defaultLocale,
            'catalogAttributes' => $catalogAttributes,
        ];
    }

    /**
     * Loại sản phẩm: physical (vật lý) / virtual (ảo) / digital (số).
     */
    private function productTypes(): array
    {
        return collect(ProductType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->all();
    }
}
