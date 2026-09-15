<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Tag\TagDTO;
use App\Http\Requests\Admin\Tag\StoreTagRequest;
use App\Http\Requests\Admin\Tag\UpdateTagRequest;
use App\Services\Admin\Tag\TagService;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TagController extends BaseController
{
    public function __construct(
        private readonly TagService $service,
        private readonly TagRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $tags = $this->service->getList(request()->all());
        return view('admin.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.tags.form');
    }

    public function store(StoreTagRequest $request)
    {
        $dto = TagDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.tags.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $tag = $this->repository->findByUuidOrFail($uuid);
        return view('admin.tags.form', compact('tag'));
    }

    public function update(UpdateTagRequest $request, string $uuid)
    {
        $dto = TagDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.tags.index')
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
