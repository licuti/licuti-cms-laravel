<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostCategory\StorePostCategoryRequest;
use App\Http\Requests\Admin\PostCategory\UpdatePostCategoryRequest;
use App\Services\Admin\PostCategory\PostCategoryService;
use App\DTOs\PostCategory\PostCategoryDTO;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Core\BulkAction\BulkActionRegistry;

class PostCategoryController extends Controller
{
    public function __construct(
        private readonly PostCategoryService $service
    ) {
    }

    public function index(Request $request, \App\Core\BulkAction\BulkActionRegistry $bulkRegistry): View
    {
        $postCategories = $this->service->getList($request->all());
        $activeLanguages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();

        $parents = $this->service->getAllActive();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        $stats = $this->service->getStats();

        $bulkActions = $bulkRegistry->getActionOptions('post_categories');

        return view('admin.post-categories.index', compact('postCategories', 'activeLanguages', 'parents', 'currentLocale', 'bulkActions', 'stats'));
    }

    public function create(Request $request): View
    {
        $parents = $this->service->getAllActive();
        $languages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        return view('admin.post-categories.form', compact('parents', 'languages', 'currentLocale'));
    }

    public function store(StorePostCategoryRequest $request): RedirectResponse
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $postCategory = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.post-categories.edit', ['uuid' => $postCategory->uuid, 'lang' => $request->query('lang')])->with('success', __('Thêm mới danh mục thành công.'));
        }

        return redirect()->route('admin.post-categories.index')->with('success', __('Thêm mới danh mục thành công.'));
    }

    public function edit(string $uuid, Request $request): View
    {
        $postCategory = $this->service->getByUuid($uuid);
        $parents = $this->service->getAllActive($postCategory->id);
        $languages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        
        return view('admin.post-categories.form', compact('postCategory', 'parents', 'languages', 'currentLocale'));
    }

    public function update(UpdatePostCategoryRequest $request, string $uuid): RedirectResponse
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.post-categories.edit', ['uuid' => $uuid, 'lang' => $request->query('lang')])->with('success', __('Cập nhật danh mục thành công.'));
        }

        return redirect()->route('admin.post-categories.index')->with('success', __('Cập nhật danh mục thành công.'));
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);
        return redirect()->route('admin.post-categories.index')->with('success', __('Xóa danh mục thành công.'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'post_categories', 
            $request->input('action'), 
            $request->input('ids')
        );

        return redirect()->route('admin.post-categories.index')
            ->with('success', __('Thao tác hàng loạt thành công.'));
    }
}
