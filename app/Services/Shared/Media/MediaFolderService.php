<?php

namespace App\Services\Shared\Media;

use App\Core\Base\BaseService;
use App\Models\MediaFolder;
use App\Repositories\Interfaces\MediaFolderRepositoryInterface;

class MediaFolderService extends BaseService
{
    public function __construct(
        private readonly MediaFolderRepositoryInterface $folderRepository
    ) {}

    public function create(array $data): MediaFolder
    {
        // Ensure parent_id is valid if provided
        if (!empty($data['parent_id'])) {
            $this->folderRepository->find($data['parent_id']); // throws if invalid
        }
        
        return $this->handleTransaction(function () use ($data) {
            return $this->folderRepository->create($data);
        });
    }

    public function delete(string $uuid): bool
    {
        return $this->handleTransaction(function () use ($uuid) {
            $folder = $this->folderRepository->findByUuid($uuid);
            return $folder->delete();
        });
    }
}
