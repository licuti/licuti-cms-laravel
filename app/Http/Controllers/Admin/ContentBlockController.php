<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\DTOs\ContentBlock\ContentBlockDTO;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\ContentBlock\StoreContentBlockRequest;
use App\Http\Requests\Admin\ContentBlock\UpdateContentBlockRequest;
use App\Models\ContentBlock;
use App\Services\Admin\ContentBlock\ContentBlockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContentBlockController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ContentBlockService $service,
    ) {}

    public function index(BulkActionRegistry $bulkRegistry): View
    {
        $this->authorize('content-blocks.view');

        return view('admin.content-blocks.index', [
            'blocks' => $this->service->getList(request()->all()),
            'tabs' => $this->service->getTabs(),
            'tab' => request('tab', 'all'),
            'bulkActions' => $bulkRegistry->getActionOptions('content_blocks'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('content-blocks.create');

        return view('admin.content-blocks.form', $this->formViewData());
    }

    public function store(StoreContentBlockRequest $request): RedirectResponse
    {
        $block = $this->service->create(ContentBlockDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.content-blocks.edit', $block->uuid)
                ->with('success', __('Thêm khối nội dung thành công.'));
        }

        return redirect()->route('admin.content-blocks.index')
            ->with('success', __('Thêm khối nội dung thành công.'));
    }

    public function edit(string $uuid): View
    {
        $this->authorize('content-blocks.view');
        $requestedLocale = request()->query('lang');

        return view('admin.content-blocks.form', $this->formViewData(
            block: $this->service->findByUuid($uuid),
            requestedLocale: is_string($requestedLocale) ? $requestedLocale : null,
        ));
    }

    public function update(UpdateContentBlockRequest $request, string $uuid): RedirectResponse
    {
        $block = $this->service->update($uuid, ContentBlockDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            $params = ['uuid' => $block->uuid];
            if (is_string($request->query('lang')) && $request->query('lang') !== '') {
                $params['lang'] = $request->query('lang');
            }

            return redirect()->route('admin.content-blocks.edit', $params)
                ->with('success', __('Cập nhật khối nội dung thành công.'));
        }

        return redirect()->route('admin.content-blocks.index')
            ->with('success', __('Cập nhật khối nội dung thành công.'));
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->authorize('content-blocks.delete');
        $this->service->delete($uuid);

        return redirect()->route('admin.content-blocks.index')
            ->with('success', __('Xóa khối nội dung thành công.'));
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): RedirectResponse
    {
        $this->authorize('content-blocks.update');
        $registry->dispatch('content_blocks', $request->input('action'), $request->input('ids'));

        return back()->with('success', __('Thao tác hàng loạt thành công.'));
    }

    private function formViewData(?ContentBlock $block = null, ?string $requestedLocale = null): array
    {
        return ['block' => $block] + $this->service->getFormOptions($block, $requestedLocale);
    }
}
