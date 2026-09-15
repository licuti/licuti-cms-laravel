<?php

namespace App\Services\Admin\Category;

use App\Core\Base\BaseService;
use App\DTOs\Category\CategoryDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryService extends BaseService

{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        $allCategories = $this->repository->getAllForList($filters);
        
        if (!empty($filters['keyword'])) {
            $treeList = $allCategories;
        } else {
            $treeList = $this->buildTreeList($allCategories);
        }
        
        $perPage = 15;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $items = $treeList->slice(($page - 1) * $perPage, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $treeList->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    public function getByUuid(string $uuid): Category
    {
        return $this->repository->findByUuid($uuid);
    }

    public function getAllActive(?int $excludeId = null)
    {
        $categories = $this->repository->getAllActive();
        return $this->buildTreeList($categories, null, '', $excludeId);
    }
    
    private function buildTreeList($categories, $parentId = null, $prefix = '', $excludeId = null)
    {
        $result = collect();
        $children = $parentId === null ? $categories->whereNull('parent_id') : $categories->where('parent_id', $parentId);
        
        foreach ($children as $category) {
            if ($excludeId !== null && $category->id === $excludeId) {
                continue;
            }
            $category->tree_name = $prefix . $category->translated_name;
            $result->push($category);
            $result = $result->merge($this->buildTreeList($categories, $category->id, $prefix . '— ', $excludeId));
        }
        return $result;
    }

    public function create(CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            if ($dto->remove_image) $data['image'] = null;

            $model = $this->repository->create($data);
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['name'])) {
                    continue;
                }

                $transData['slug'] = $this->generateUniqueSlug(
                    translationTable: 'category_translations',
                    locale: $locale,
                    slug: $transData['slug'] ?? null,
                    title: $transData['name'],
                    ignoreForeignId: $model->id,
                    foreignKey: 'category_id'
                );
                
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);

                // TODO (Future): Move to CategoryTranslationRepository to maintain strict Repository pattern
                $model->translations()->create(array_merge($transData, ['locale' => $locale]));
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function update(string $uuid, CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $data = $dto->toArray();
            if ($dto->remove_image) $data['image'] = null;
            $this->repository->update($model->id, $data);
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['name'])) {
                    continue;
                }

                $transData['slug'] = $this->generateUniqueSlug(
                    translationTable: 'category_translations',
                    locale: $locale,
                    slug: $transData['slug'] ?? null,
                    title: $transData['name'],
                    ignoreForeignId: $model->id,
                    foreignKey: 'category_id'
                );
                
                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);

                // TODO (Future): Move to CategoryTranslationRepository to maintain strict Repository pattern
                $model->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $transData
                );
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuid($uuid);
            
            // Cập nhật category_id = null cho các sản phẩm
            DB::table('products')->where('category_id', $model->id)->update(['category_id' => null]);
            
            // Đẩy danh mục con lên 1 cấp
            $model->children()->update(['parent_id' => $model->parent_id]);
            
            return $this->repository->delete($model->id);
        });
    }

    public function bulkAction(string $action, array $uuids): void
    {
        DB::transaction(function () use ($action, $uuids) {
            foreach ($uuids as $uuid) {
                $category = $this->repository->findByUuid($uuid);
                if (!$category) continue;

                if ($action === 'delete') {
                    $this->delete($uuid);
                } elseif ($action === 'status_1') {
                    $this->repository->update($category->id, ['is_active' => true]);
                } elseif ($action === 'status_0') {
                    $this->repository->update($category->id, ['is_active' => false]);
                }
            }
        });
    }
}
