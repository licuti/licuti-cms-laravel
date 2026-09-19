<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;

use App\Models\MediaFolder;
use App\Repositories\Interfaces\MediaFolderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MediaFolderRepository extends BaseRepository implements MediaFolderRepositoryInterface
{
    public function __construct(MediaFolder $model)
    {
        parent::__construct($model);
    }

    public function getSubFolders(?int $parentId = null): Collection
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getBreadcrumbs(int $folderId): array
    {
        $breadcrumbs = [];
        $currentFolder = $this->model->find($folderId);

        while ($currentFolder) {
            array_unshift($breadcrumbs, [
                'id' => $currentFolder->id,
                'uuid' => $currentFolder->uuid,
                'name' => $currentFolder->name,
            ]);
            
            if ($currentFolder->parent_id) {
                $currentFolder = $this->model->find($currentFolder->parent_id);
            } else {
                $currentFolder = null;
            }
        }

        return $breadcrumbs;
    }
}
