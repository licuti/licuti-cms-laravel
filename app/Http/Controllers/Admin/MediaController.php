<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Repositories\Interfaces\MediaFolderRepositoryInterface;
use App\Services\Shared\Media\MediaService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly MediaFolderRepositoryInterface $folderRepository
    ) {}

    public function index(Request $request)
    {
        $this->authorize('media.view');

        $filters = $request->only(['keyword', 'mime_type', 'sort']);

        // Map UI type to mime_type logic
        $type = $request->input('type');
        if ($type === 'image') {
            $filters['mime_type'] = 'image';
        }
        if ($type === 'document') {
            $filters['mime_type'] = 'application';
        }
        if ($type === 'video') {
            $filters['mime_type'] = 'video';
        }

        $folderUuid = $request->input('folder');
        $folderId = null;
        if ($folderUuid) {
            try {
                $folder = $this->folderRepository->findByUuid($folderUuid);
                $folderId = $folder->id;
            } catch (\Exception $e) {
                // Invalid UUID, fallback to root
            }
        }
        $filters['folder_id'] = $folderId;

        $media = $this->mediaService->getPaginated($filters, 12);

        $folders = $this->folderRepository->getSubFolders($folderId);
        $breadcrumbs = $folderId ? $this->folderRepository->getBreadcrumbs($folderId) : [];

        if ($request->ajax() || $request->wantsJson()) {
            $media->getCollection()->transform(function ($item) {
                return $this->transformMedia($item);
            });

            return response()->json([
                'media' => $media,
                'folders' => $folders,
                'breadcrumbs' => $breadcrumbs,
            ]);
        }

        return view('admin.media.index', compact('media', 'filters'));
    }

    public function store(Request $request)
    {
        $this->authorize('media.upload');

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg,pdf,doc,docx,xls,xlsx,csv,zip,rar,txt,mp4|max:10240',
        ]);

        $file = $request->file('file');
        $folderUuid = $request->input('folder_id', null);
        $folderId = null;

        if ($folderUuid) {
            try {
                $folderId = $this->folderRepository->findByUuid($folderUuid)->id;
            } catch (\Exception $e) {
            }
        }

        $media = $this->mediaService->upload($file, $folderId, ['alt_text' => $request->input('title')]);

        // Transform for UI
        $media = $this->transformMedia($media);

        return response()->json($media);
    }

    /**
     * Attach display-only attributes (URL, thumb, size, extension, date)
     * needed by the media picker UI without changing Media model serialization.
     */
    private function transformMedia(Media $item): Media
    {
        $item->url = $item->getUrl();
        $item->conversions = ['thumb' => $item->getThumbUrl()];
        $item->human_readable_size = $this->formatBytes($item->size);
        $item->extension = strtoupper(pathinfo($item->original_name ?? $item->file_name, PATHINFO_EXTENSION));
        $item->formatted_date = $item->created_at?->format('d/m/Y H:i');

        return $item;
    }

    private function formatBytes(?int $bytes): string
    {
        if (! $bytes) {
            return '';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $exp = min(floor(log($bytes, 1024)), 3);
        $precision = $exp === 0 ? 0 : ($exp === 1 ? 1 : 2);

        return round($bytes / pow(1024, $exp), $precision).' '.$units[$exp];
    }

    public function destroy(string $uuid, Request $request)
    {
        $this->authorize('media.delete');

        $this->mediaService->delete($uuid);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.media.index')->with('success', 'Xóa file thành công.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('media.delete');

        $ids = $request->input('ids', []);

        $count = 0;
        foreach ($ids as $id) {
            if ($this->mediaService->delete($id)) {
                $count++;
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return redirect()->route('admin.media.index')->with('success', "Đã xóa thành công {$count} file.");
    }
}
