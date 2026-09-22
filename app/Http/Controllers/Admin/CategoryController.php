<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\BulkAction\BulkActionRegistry;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Services\Admin\Category\CategoryService;
use App\DTOs\Category\CategoryDTO;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service
    ) {
    }

    public function index(Request $request, BulkActionRegistry $bulkRegistry): View
    {
        $categories = $this->service->getList($request->all());
        $activeLanguages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        
        $parents = $this->service->getAllActive();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        $languages = $activeLanguages;
        $bulkActions = $bulkRegistry->getActionOptions('categories');
        
        return view('admin.categories.index', compact('categories', 'activeLanguages', 'parents', 'currentLocale', 'languages', 'bulkActions'));
    }

    public function create(Request $request): View
    {
        $parents = $this->service->getAllActive();
        $languages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        return view('admin.categories.form', compact('parents', 'languages', 'currentLocale'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $dto = CategoryDTO::fromRequest($request);
        $category = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.categories.edit', ['uuid' => $category->uuid, 'lang' => $request->query('lang')])->with('success', __('Thêm mới danh mục thành công.'));
        }

        return redirect()->route('admin.categories.index')->with('success', __('Thêm mới danh mục thành công.'));
    }

    public function edit(string $uuid, Request $request): View
    {
        $category = $this->service->getByUuid($uuid);
        $parents = $this->service->getAllActive($category->id);
        $languages = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        $currentLocale = $request->query('lang', session('admin_content_locale', config('app.locale', 'vi')));
        
        return view('admin.categories.form', compact('category', 'parents', 'languages', 'currentLocale'));
    }

    public function update(UpdateCategoryRequest $request, string $uuid): RedirectResponse
    {
        $dto = CategoryDTO::fromRequest($request);
        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.categories.edit', ['uuid' => $uuid, 'lang' => $request->query('lang')])->with('success', __('Cập nhật danh mục thành công.'));
        }

        return redirect()->route('admin.categories.index')->with('success', __('Cập nhật danh mục thành công.'));
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);
        return redirect()->route('admin.categories.index')->with('success', __('Xóa danh mục thành công.'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'categories',
            $request->input('action'),
            $request->input('ids')
        );

        return redirect()->route('admin.categories.index')->with('success', __('Thao tác hàng loạt thành công.'));
    }
}
