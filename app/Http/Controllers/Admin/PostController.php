<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\Post\PostDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\Post\StorePostRequest;
use App\Http\Requests\Admin\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\Admin\Post\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends BaseController
{
    public function __construct(
        private readonly PostService $service,
        private readonly PostRepositoryInterface $repository,
        private readonly PostCategoryRepositoryInterface $categoryRepository,
        private readonly LanguageRepositoryInterface $languageRepository
    ) {}

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        return view('admin.posts.index', [
            'posts' => $this->service->getList(request()->all()),
            'tabs' => $this->service->getTabs(),
            'tab' => request('tab', 'all'),
            'statuses' => $this->service->getStatusOptions(),
            'categories' => $this->categoryRepository->getAllActive(),
            'bulkActions' => $bulkRegistry->getActionOptions('posts'),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', $this->formViewData());
    }

    public function store(StorePostRequest $request): RedirectResponse
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
        $requestedLocale = request()->query('lang');

        return view('admin.posts.form', $this->formViewData(
            post: $this->repository->findByUuid($uuid, ['*'], ['translations', 'imageMedia', 'categories']),
            requestedLocale: is_string($requestedLocale) ? $requestedLocale : null
        ));
    }

    public function update(UpdatePostRequest $request, string $uuid): RedirectResponse
    {
        $post = $this->service->update($uuid, PostDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            $params = ['uuid' => $post->uuid];
            if (is_string($request->query('lang')) && $request->query('lang') !== '') {
                $params['lang'] = $request->query('lang');
            }

            return redirect()->route('admin.posts.edit', $params)->with('success', 'Cập nhật bài viết thành công.');
        }

        return redirect()->route('admin.posts.index')->with('success', 'Cập nhật bài viết thành công.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Xóa bài viết thành công.');
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $registry->dispatch(
            'posts',
            $request->input('action'),
            $request->input('ids')
        );

        return back()->with('success', 'Thao tác hàng loạt thành công.');
    }

    /**
     * Dữ liệu dùng chung cho create/edit form.
     * Tránh lặp code 2 lần trong create() và edit().
     */
    private function formViewData(?Post $post = null, ?string $requestedLocale = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale = $defaultLanguage?->code ?? app()->getLocale();

        // Bài viết mới luôn soạn bằng ngôn ngữ mặc định; bản dịch thêm sau khi đã lưu.
        $currentLocale = $post
            && is_string($requestedLocale)
            && $activeLanguages->contains('code', $requestedLocale)
                ? $requestedLocale
                : $defaultLocale;

        return [
            'post' => $post,
            'statuses' => $this->service->getStatusOptions(),
            'categories' => $this->categoryRepository->getTree(),
            'authors' => User::active()->get(),
            'activeLanguages' => $activeLanguages,
            'languages' => $activeLanguages,
            'defaultLocale' => $defaultLocale,
            'currentLocale' => $currentLocale,
        ];
    }
}
