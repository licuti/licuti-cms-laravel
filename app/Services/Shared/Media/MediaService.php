<?php

namespace App\Services\Shared\Media;

use App\Core\Base\BaseService;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Models\Media;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class MediaService extends BaseService
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->mediaRepository->getPaginatedMedia($filters, $perPage);
    }

    public function getByUuid(string $uuid): Media
    {
        try {
            $media = $this->mediaRepository->findByUuid($uuid);
            if (!$media instanceof Media) {
                throw new BusinessException("Không tìm thấy tệp tin với mã UUID: {$uuid}", 404);
            }
            return $media;
        } catch (\Throwable) {
            throw new BusinessException("Không tìm thấy tệp tin với mã UUID: {$uuid}", 404);
        }
    }

    public function upload(
        UploadedFile $file,
        ?int $folderId = null,
        array $customProperties = []
    ): Media {
        $user = request()->user() ?? auth()->user();
        
        $disk = 'public';
        $fileName = $this->generateUniqueFileName($file);
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $originalName = $file->getClientOriginalName();
        
        // Store original file manually to avoid Windows getRealPath() bug on Temp files
        $path = 'media/original/' . $fileName;
        $stream = fopen($file->getPathname(), 'r');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        $thumbPath = null;
        
        // Generate thumb for images
        if (str_starts_with($mimeType, 'image/')) {
            try {
                // Read from the moved file location, NOT the temp file
                $imagePath = Storage::disk($disk)->path($path);
                $image = Image::read($imagePath);
                $image->cover(150, 150);
                
                $thumbFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                $thumbPath = 'media/thumb/' . $thumbFileName;
                Storage::disk($disk)->put($thumbPath, (string) $image->toWebp(80));
            } catch (\Throwable $e) {
                // Ignore thumb generation error (catch Throwable to catch Errors too)
                \Illuminate\Support\Facades\Log::error("Thumb generation failed: " . $e->getMessage());
            }
        }

        try {
            return $this->handleTransaction(function () use ($folderId, $user, $customProperties, $disk, $path, $thumbPath, $fileName, $originalName, $mimeType, $size) {
                return $this->mediaRepository->create([
                    'folder_id' => $folderId,
                    'user_id' => $user?->id,
                    'disk' => $disk,
                    'file_path' => $path,
                    'thumb_path' => $thumbPath,
                    'file_name' => $fileName,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'alt_text' => $customProperties['alt_text'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            // Rollback files if DB insert fails
            Storage::disk($disk)->delete(array_filter([$path, $thumbPath]));
            throw $e;
        }
    }

    public function uploadMultiple(
        array $files,
        ?int $folderId = null,
        array $customProperties = []
    ): Collection {
        $uploadedMedia = new Collection();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedMedia->push($this->upload($file, $folderId, $customProperties));
            }
        }

        return $uploadedMedia;
    }

    public function delete(string $uuid): bool
    {
        try {
            $media = $this->mediaRepository->findByUuid($uuid);
            return $media->delete();
        } catch (\Throwable) {
            return false;
        }
    }

    private function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        return now()->format('Ymd_His_') . uniqid() . '.' . $extension;
    }
}
