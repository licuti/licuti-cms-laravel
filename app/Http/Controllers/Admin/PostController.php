<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\Post\PostDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\Post\StorePostRequest;
use App\Http\Requests\Admin\Post\UpdatePostRequest;
use App\Models\PostCategory;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\Admin\Post\PostService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends BaseController
{
    public function __construct(
        private readonly PostService $service,
        private readonly PostRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        return view('admin.posts.index', [
            'posts'        => $this->service->getList(request()->all()),
            'tabs'         => $this->service->getTabs(),
            'tab'          => request('tab', 'all'),
            'statuses'     => collect(\App\Core\Enums\PostStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'categories'   => $this->getCategories(),
            'bulkActions'  => $bulkRegistry->getActionOptions('posts'),
        ]);
    }

    public function create(): View
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return view('admin.posts.form', [
            'statuses'        => collect(\App\Core\Enums\PostStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'categories'      => $this->getCategories(),
            'authors'         => \App\Models\User::active()->get(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $post = $this->service->create(PostDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.posts.edit', $post->uuid)
                ->with('success', 'Thêm bài viết thành công.');
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Thêm bài viết thành công.');
    }

    public function edit(string $uuid): View
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale   = $defaultLanguage?->code ?? app()->getLocale();

        return view('admin.posts.form', [
            'post'            => $this->repository->findByUuid($uuid, ['*'], ['translations', 'imageMedia', 'categories']),
            'statuses'        => collect(\App\Core\Enums\PostStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            'categories'      => $this->getCategories(),
            'authors'         => \App\Models\User::active()->get(),
            'activeLanguages' => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
        ]);
    }

    public function update(UpdatePostRequest $request, string $uuid)
    {
        $post = $this->service->update($uuid, PostDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.posts.edit', $post->uuid)->with('success', 'Cập nhật bài viết thành công.');
        }

        return redirect()->route('admin.posts.index')->with('success', 'Cập nhật bài viết thành công.');
    }

    public function destroy(string $uuid)
    {
        $this->service->delete($uuid);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Xóa bài viết thành công.');
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry)
    {
        $registry->dispatch(
            'posts', 
            $request->input('action'), 
            $request->input('ids')
        );

        return back()->with('success', "Thao tác hàng loạt thành công.");
    }

    /** Danh mục dùng cho form chọn chuyên mục — trả về cây phân cấp sẵn */
    private function getCategories(): \Illuminate\Support\Collection
    {
        return PostCategory::toTree();
    }
}
