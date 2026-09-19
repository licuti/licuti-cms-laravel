<?php

namespace App\Services\Admin\PostCategory;

use App\Core\Base\BaseService;
use App\DTOs\PostCategory\PostCategoryDTO;
use App\Models\PostCategory;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PostCategoryService extends BaseService
{
    public function __construct(
        private readonly PostCategoryRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): Collection
    {
        $allCategories = $this->repository->getAllForList($filters);
        
        if (!empty($filters['keyword'])) {
            return $allCategories;
        }
        
        return $this->buildTreeList($allCategories);
    }

    public function getByUuid(string $uuid): PostCategory
    {
        return $this->repository->findByUuid($uuid);
    }

    public function getAllActive(?int $excludeId = null): Collection
    {
        $postCategories = $this->repository->getAllActive();

        if ($excludeId !== null) {
            $descendantIds = $this->getDescendantIds($postCategories, $excludeId);
            $excludeIds = array_merge([$excludeId], $descendantIds);
            $postCategories = $postCategories->whereNotIn('id', $excludeIds);
        }

        return $this->buildTreeList($postCategories);
    }

    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    private function getDescendantIds(Collection $categories, int $parentId): array
    {
        $descendants = [];
        $children = $categories->where('parent_id', $parentId);
        foreach ($children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendantIds($categories, $child->id));
        }
        return $descendants;
    }
    
    private function buildTreeList(Collection $postCategories, $parentId = null, $prefix = ''): Collection
    {
        $result = collect();
        $children = $parentId === null ? $postCategories->whereNull('parent_id') : $postCategories->where('parent_id', $parentId);
        
        foreach ($children as $postCategory) {
            $postCategory->tree_name = $prefix . $postCategory->translated_name;
            $result->push($postCategory);
            $result = $result->merge($this->buildTreeList($postCategories, $postCategory->id, $prefix . '— '));
        }
        return $result;
    }

    public function create(PostCategoryDTO $dto): PostCategory
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();
            if ($dto->remove_image) $data['image'] = null;

            $model = $this->repository->create($data);
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['slug']) && !empty($transData['name'])) {
                    $transData['slug'] = Str::slug($transData['name']);
                }
                
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);

                // TODO (Future): Move to CategoryTranslationRepository to maintain strict Repository pattern
                $model->translations()->create(array_merge($transData, ['locale' => $locale]));
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function update(string $uuid, PostCategoryDTO $dto): PostCategory
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $data = $dto->toArray();
            if ($dto->remove_image) $data['image'] = null;
            $this->repository->update($model->id, $data);
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['slug']) && !empty($transData['name'])) {
                    $transData['slug'] = Str::slug($transData['name']);
                }
                
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);

                // TODO (Future): Move to CategoryTranslationRepository to maintain strict Repository pattern
                $model->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $transData
                );
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model->fresh();
        });
    }

    public function delete(string $uuid): bool
    {
        return $this->handleTransaction(function () use ($uuid) {
            $model = $this->repository->findByUuid($uuid);
            
            // Đẩy danh mục con lên 1 cấp
            $model->children()->update(['parent_id' => $model->parent_id]);
            
            return $this->repository->delete($model->id);
        });
    }
}
