<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Services\Shared\Media\MediaFolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MediaFolderController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly MediaFolderService $folderService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $this->authorize('media.upload');

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|string|exists:media_folders,uuid',
        ]);

        $data = $request->only(['name']);
        $parentUuid = $request->input('parent_id');
        $parentId = null;
        if ($parentUuid) {
            $parentId = \App\Models\MediaFolder::where('uuid', $parentUuid)->value('id');
        }
        $data['parent_id'] = $parentId;
        $data['slug'] = Str::slug($data['name']);
        $data['user_id'] = $request->user()?->id;

        $folder = $this->folderService->create($data);

        return $this->successResponse(
            message: 'Tạo thư mục thành công',
            data: $folder
        );
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->authorize('media.delete');

        $this->folderService->delete($uuid);

        return $this->successResponse(
            message: 'Xóa thư mục thành công'
        );
    }
}
