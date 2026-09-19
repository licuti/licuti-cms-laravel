<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Banner\BannerDTO;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\Admin\Banner\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends BaseController
{
    public function __construct(
        private readonly BannerService $service,
        private readonly BannerRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {
    }

    public function index(Request $request): View
    {
        $banners = $this->service->getList($request->all());
        $positions = $this->positions();

        return view('admin.banners.index', compact('banners', 'positions'));
    }

    public function create(): View
    {
        return view('admin.banners.form', $this->formViewData());
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $dto = BannerDTO::fromRequest($request);
        $banner = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.banners.edit', $banner->uuid)
                ->with('success', __('Thêm mới banner thành công.'));
        }

        return redirect()->route('admin.banners.index')
            ->with('success', __('Thêm mới banner thành công.'));
    }

    public function edit(string $uuid): View
    {
        $banner = $this->repository->findByUuidWithRelations($uuid);
        return view('admin.banners.form', $this->formViewData($banner));
    }

    public function update(UpdateBannerRequest $request, string $uuid): RedirectResponse
    {
        $dto = BannerDTO::fromRequest($request);
        $banner = $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.banners.edit', $banner->uuid)
                ->with('success', __('Cập nhật banner thành công.'));
        }

        return redirect()->route('admin.banners.index')
            ->with('success', __('Cập nhật banner thành công.'));
    }

    public function destroy(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa banner thành công.'));
            }

            return redirect()->route('admin.banners.index')
                ->with('success', __('Xóa banner thành công.'));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }

            return redirect()->route('admin.banners.index')
                ->with('error', $e->getMessage());
        }
    }

    private function positions(): array
    {
        return [
            'home_slider'   => __('Slider trang chủ'),
            'home_banner_1' => __('Banner trang chủ (Vị trí 1)'),
            'home_banner_2' => __('Banner trang chủ (Vị trí 2)'),
            'sidebar'       => __('Thanh bên (Sidebar)'),
            'popup'         => __('Cửa sổ bật lên (Popup)'),
        ];
    }

    /**
     * Dữ liệu dùng chung cho create/edit form banner.
     */
    private function formViewData(?Banner $banner = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return [
            'banner'          => $banner,
            'positions'       => $this->positions(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }
}
