<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Product\ProductDTO;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
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
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly BrandRepositoryInterface $brandRepository
    ) {
    }

    public function index(Request $request): View
    {
        $products = $this->service->getList($request->all());
        $categories = $this->categoryRepository->all();
        $brands = $this->brandRepository->all();
        $statuses = $this->statuses();
        $tabs = $this->getTabs();
        $bulkActions = $this->bulkActions();
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
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }

            return redirect()->route('admin.products.index')
                ->with('error', $e->getMessage());
        }
    }

    public function bulk(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', __('Không có sản phẩm nào được chọn.'));
        }

        try {
            $products = Product::whereIn('id', $ids)->get();

            switch ($action) {
                case 'delete':
                    foreach ($products as $product) {
                        $this->service->delete($product->uuid);
                    }
                    break;
                case 'publish':
                    $products->each(fn($p) => $p->update(['status' => 'published']));
                    break;
                case 'draft':
                    $products->each(fn($p) => $p->update(['status' => 'draft']));
                    break;
                case 'archive':
                    $products->each(fn($p) => $p->update(['status' => 'archived']));
                    break;
                default:
                    return back()->with('error', __('Thao tác không hợp lệ.'));
            }

            return redirect()->route('admin.products.index')
                ->with('success', __('Thao tác hàng loạt thành công.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function statuses(): array
    {
        return [
            'published' => __('Đã xuất bản'),
            'draft'     => __('Bản nháp'),
            'archived'  => __('Lưu trữ'),
        ];
    }

    private function getTabs(): array
    {
        $counts = [
            'published' => Product::where('status', 'published')->count(),
            'draft'     => Product::where('status', 'draft')->count(),
            'archived'  => Product::where('status', 'archived')->count(),
        ];

        return [
            ['key' => 'all', 'label' => 'Tất cả', 'count' => array_sum($counts)],
            ['key' => 'published', 'label' => 'Đã xuất bản', 'count' => $counts['published']],
            ['key' => 'draft', 'label' => 'Bản nháp', 'count' => $counts['draft']],
            ['key' => 'archived', 'label' => 'Lưu trữ', 'count' => $counts['archived']],
        ];
    }

    private function bulkActions(): array
    {
        return [
            'delete' => __('Xóa đã chọn'),
            'publish' => __('Chuyển trạng thái: Đã xuất bản'),
            'draft' => __('Chuyển trạng thái: Bản nháp'),
            'archive' => __('Chuyển trạng thái: Lưu trữ'),
        ];
    }

    private function formViewData(?Product $product = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return [
            'product'         => $product,
            'categories'      => $this->categoryRepository->all(),
            'brands'          => $this->brandRepository->all(),
            'statuses'        => $this->statuses(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }
}
