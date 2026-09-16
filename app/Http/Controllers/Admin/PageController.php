<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\Page\PageDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\Page\StorePageRequest;
use App\Http\Requests\Admin\Page\UpdatePageRequest;
use App\Models\Page;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\Admin\Page\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends BaseController
{
    public function __construct(
        private readonly PageService $service,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        return view('admin.pages.index', [
            'pages'       => $this->service->getList(request()->all()),
            'tabs'        => $this->service->getTabs(),
            'tab'         => request('tab', 'all'),
            'statuses'    => $this->service->getStatusOptions(),
            'bulkActions' => $bulkRegistry->getActionOptions('pages'),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', $this->formViewData());
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = $this->service->create(PageDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.pages.edit', $page->uuid)
                ->with('success', __('Thêm trang tĩnh thành công.'));
        }

        return redirect()->route('admin.pages.index')
            ->with('success', __('Thêm trang tĩnh thành công.'));
    }

    public function edit(string $uuid): View
    {
        return view('admin.pages.form', $this->formViewData(
            page: $this->service->findByUuid($uuid)
        ));
    }

    public function update(UpdatePageRequest $request, string $uuid): RedirectResponse
    {
        $this->service->update($uuid, PageDTO::fromRequest($request));

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

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'pages',
            $request->input('action'),
            $request->input('ids')
        );

        return back()->with('success', __('Thao tác hàng loạt thành công.'));
    }

    /** Dữ liệu dùng chung cho create/edit form */
    private function formViewData(?Page $page = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return [
            'page'            => $page,
            'statuses'        => $this->service->getStatusOptions(),
            'templates'       => $this->service->getTemplateOptions(),
            'pageTree'        => $this->service->getParentOptions($page?->id),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ];
    }
}
