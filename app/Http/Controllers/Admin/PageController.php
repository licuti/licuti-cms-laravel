<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Page\PageDTO;
use App\Http\Requests\Admin\Page\StorePageRequest;
use App\Http\Requests\Admin\Page\UpdatePageRequest;
use App\Services\Admin\Page\PageService;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends BaseController
{
    public function __construct(
        private readonly PageService $service
    ) {
    }

    public function index(): View
    {
        $pages = $this->service->getList(request()->all());
        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $languages = \App\Models\Language::active()->ordered()->get();
        return view('admin.pages.form', compact('languages'));
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $dto = PageDTO::fromRequest($request);
        $page = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.pages.edit', $page->uuid)
                ->with('success', __('Thêm trang tĩnh thành công.'));
        }

        return redirect()->route('admin.pages.index')
            ->with('success', __('Thêm trang tĩnh thành công.'));
    }

    public function edit(string $uuid): View
    {
        $page = $this->service->findByUuid($uuid);
        $languages = \App\Models\Language::active()->ordered()->get();
        return view('admin.pages.form', compact('page', 'languages'));
    }

    public function update(UpdatePageRequest $request, string $uuid): RedirectResponse
    {
        $dto = PageDTO::fromRequest($request);
        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.pages.edit', $uuid)
                ->with('success', __('Cập nhật trang tĩnh thành công.'));
        }

        return redirect()->route('admin.pages.index')
            ->with('success', __('Cập nhật trang tĩnh thành công.'));
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);
        return redirect()->route('admin.pages.index')
            ->with('success', __('Xóa trang tĩnh thành công.'));
    }
}
