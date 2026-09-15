<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Menu\MenuDTO;
use App\Http\Requests\Admin\Menu\StoreMenuRequest;
use App\Http\Requests\Admin\Menu\UpdateMenuRequest;
use App\Services\Admin\Menu\MenuService;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MenuController extends BaseController
{
    public function __construct(
        private readonly MenuService $service,
        private readonly MenuRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $menus = $this->service->getList(request()->all());
        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('admin.menus.form');
    }

    public function store(StoreMenuRequest $request)
    {
        $dto = MenuDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.menus.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $menu = $this->repository->findByUuidOrFail($uuid);
        return view('admin.menus.form', compact('menu'));
    }

    public function update(UpdateMenuRequest $request, string $uuid)
    {
        $dto = MenuDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.menus.index')
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
