<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Banner\BannerDTO;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Services\Admin\Banner\BannerService;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BannerController extends BaseController
{
    public function __construct(
        private readonly BannerService $service,
        private readonly BannerRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $banners = $this->service->getList(request()->all());
        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.banners.form');
    }

    public function store(StoreBannerRequest $request)
    {
        $dto = BannerDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.banners.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $banner = $this->repository->findByUuidOrFail($uuid);
        return view('admin.banners.form', compact('banner'));
    }

    public function update(UpdateBannerRequest $request, string $uuid)
    {
        $dto = BannerDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.banners.index')
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
