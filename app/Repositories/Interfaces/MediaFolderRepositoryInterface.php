<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface MediaFolderRepositoryInterface extends BaseRepositoryInterface
{
    public function getSubFolders(?int $parentId = null): Collection;
    
    public function getBreadcrumbs(int $folderId): array;
}
