<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Menu\MenuDTO;
use App\Http\Requests\Admin\Menu\StoreMenuRequest;
use App\Http\Requests\Admin\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use App\Services\Admin\Menu\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends BaseController
{
    public function __construct(
        private readonly MenuService $service,
        private readonly MenuRepositoryInterface $repository
    ) {
    }

    public function index(Request $request): View
    {
        $menus = $this->service->getList($request->all());
        $locations = $this->locations();

        return view('admin.menus.index', compact('menus', 'locations'));
    }

    public function create(): View
    {
        return view('admin.menus.form', $this->formViewData());
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $dto = MenuDTO::fromRequest($request);
        $menu = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.menus.edit', $menu->uuid)
                ->with('success', __('Thêm menu thành công.'));
        }

        return redirect()->route('admin.menus.index')
            ->with('success', __('Thêm menu thành công.'));
    }

    public function edit(string $uuid): View
    {
        $menu = $this->repository->findByUuidWithRelations($uuid);
        return view('admin.menus.form', $this->formViewData($menu));
    }

    public function update(UpdateMenuRequest $request, string $uuid): RedirectResponse
    {
        $dto = MenuDTO::fromRequest($request);
        $menu = $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.menus.edit', $menu->uuid)
                ->with('success', __('Cập nhật menu thành công.'));
        }

        return redirect()->route('admin.menus.index')
            ->with('success', __('Cập nhật menu thành công.'));
    }

    public function destroy(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        try {
            $this->service->delete($uuid);

            if ($request->wantsJson()) {
                return $this->successResponse(message: __('Xóa menu thành công.'));
            }

            return redirect()->route('admin.menus.index')
                ->with('success', __('Xóa menu thành công.'));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->errorResponse($e->getMessage());
            }

            return redirect()->route('admin.menus.index')
                ->with('error', $e->getMessage());
        }
    }

    private function locations(): array
    {
        return [
            'header'  => __('Menu đầu trang (Header)'),
            'footer'  => __('Menu chân trang (Footer)'),
            'mobile'  => __('Menu di động (Mobile)'),
            'sidebar' => __('Menu thanh bên (Sidebar)'),
        ];
    }

    private function formViewData(?Menu $menu = null): array
    {
        return [
            'menu'      => $menu,
            'locations' => $this->locations(),
        ];
    }
}
