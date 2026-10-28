<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\FlashSale\FlashSaleDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\FlashSale\StoreFlashSaleRequest;
use App\Http\Requests\Admin\FlashSale\UpdateFlashSaleRequest;
use App\Models\FlashSale;
use App\Models\Product;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use App\Services\Admin\FlashSale\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlashSaleController extends BaseController
{
    public function __construct(
        private readonly FlashSaleService $service,
        private readonly FlashSaleRepositoryInterface $repository,
        private readonly \App\Repositories\Interfaces\LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(Request $request, BulkActionRegistry $bulkRegistry): View
    {
        $currentTab = $request->input('tab', $request->input('status', 'all'));
        $filters = [
            'status' => $currentTab,
            'search' => $request->input('search', $request->input('keyword')),
        ];

        $flashSales = $this->service->getList($filters);
        $filterTabs = $this->service->getFilterTabs($currentTab);
        $bulkActions = $bulkRegistry->getActionOptions('flash_sales');

        return view('admin.flash-sales.index', [
            'flashSales'  => $flashSales,
            'filterTabs'  => $filterTabs,
            'currentTab'  => $currentTab,
            'bulkActions' => $bulkActions,
        ]);
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        try {
            $registry->dispatch(
                'flash_sales',
                $request->input('action'),
                $request->input('ids')
            );

            return back()->with('success', __('Thao tác hàng loạt thành công.'));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function create(): View
    {
        return view('admin.flash-sales.form', $this->formViewData());
    }

    public function store(StoreFlashSaleRequest $request): RedirectResponse
    {
        $dto = FlashSaleDTO::fromRequest($request);
        $flashSale = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.flash-sales.edit', $flashSale->uuid)
                ->with('success', __('Thêm mới chiến dịch Flash Sale thành công.'));
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', __('Thêm mới chiến dịch Flash Sale thành công.'));
    }

    public function edit(string $uuid): View
    {
        $flashSale = $this->repository->findByUuidOrFail($uuid);

        return view('admin.flash-sales.form', $this->formViewData($flashSale));
    }

    private function formViewData(?FlashSale $flashSale = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        $products = Product::with(['translations', 'variants.attributeValues'])
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

        return [
            'flashSale'       => $flashSale,
            'isEdit'          => (bool) $flashSale,
            'products'        => $products,
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }

    public function update(UpdateFlashSaleRequest $request, string $uuid): RedirectResponse
    {
        $dto = FlashSaleDTO::fromRequest($request);
        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.flash-sales.edit', $uuid)
                ->with('success', __('Cập nhật chiến dịch Flash Sale thành công.'));
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', __('Cập nhật chiến dịch Flash Sale thành công.'));
    }

    public function toggleStatus(string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $isActive = $this->service->toggleStatus($uuid);

            if (request()->wantsJson()) {
                return $this->successResponse(
                    message: __('Cập nhật trạng thái thành công.'),
                    data: ['is_active' => $isActive]
                );
            }

            return back()->with('success', __('Cập nhật trạng thái thành công.'));
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse($e->getMessage(), 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if (request()->wantsJson()) {
                return $this->successResponse(message: __('Xóa chiến dịch Flash Sale thành công.'));
            }

            return redirect()->route('admin.flash-sales.index')
                ->with('success', __('Xóa chiến dịch Flash Sale thành công.'));
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return $this->errorResponse($e->getMessage(), 400);
            }

            return redirect()->route('admin.flash-sales.index')
                ->with('error', $e->getMessage());
        }
    }
}
