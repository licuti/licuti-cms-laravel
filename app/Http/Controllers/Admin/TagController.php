<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Tag\TagDTO;
use App\Http\Requests\Admin\Tag\StoreTagRequest;
use App\Http\Requests\Admin\Tag\UpdateTagRequest;
use App\Services\Admin\Tag\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends BaseController
{
    public function __construct(
        private readonly TagService $service
    ) {}

    public function index(Request $request): View
    {
        return view('admin.tags.index', [
            'tags' => $this->service->getList($request->all()),
        ]);
    }

    public function create(): View
    {
        return view('admin.tags.form');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $dto = TagDTO::fromRequest($request);
        $tag = $this->service->create($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.tags.edit', $tag->uuid)
                ->with('success', __('Thêm thẻ tag thành công.'));
        }

        return redirect()->route('admin.tags.index')
            ->with('success', __('Thêm thẻ tag thành công.'));
    }

    public function edit(string $uuid): View
    {
        $tag = $this->service->findByUuid($uuid);

        return view('admin.tags.form', compact('tag'));
    }

    public function update(UpdateTagRequest $request, string $uuid): RedirectResponse
    {
        $dto = TagDTO::fromRequest($request);
        $this->service->update($uuid, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.tags.edit', $uuid)
                ->with('success', __('Cập nhật thẻ tag thành công.'));
        }

        return redirect()->route('admin.tags.index')
            ->with('success', __('Cập nhật thẻ tag thành công.'));
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->service->delete($uuid);

        return redirect()->route('admin.tags.index')
            ->with('success', __('Xóa thẻ tag thành công.'));
    }
}
