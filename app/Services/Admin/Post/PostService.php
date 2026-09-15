<?php

namespace App\Services\Admin\Post;

use App\Core\Base\BaseService;
use App\DTOs\Post\PostDTO;
use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Core\Enums\PostStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    public function __construct(
        private readonly PostRepositoryInterface $repository
    ) {}

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters);
    }

    public function create(PostDTO $dto): Post
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['author_id'] = $data['author_id'] ?? auth()->id();

            $post = $this->repository->create($data);
            $post->categories()->sync($dto->categoryIds);
            
            $this->saveTranslations($post, $dto->translations);

            return $post;
        });
    }

    public function update(string $uuid, PostDTO $dto): Post
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $post = $this->repository->findByUuid($uuid);
            $this->repository->update($post->id, $dto->toArray());
            
            $post->categories()->sync($dto->categoryIds);
            $this->saveTranslations($post, $dto->translations);

            return $post->fresh();
        });
    }

    public function delete(string $uuid): bool
    {
        $post = $this->repository->findByUuid($uuid);
        return $this->repository->delete($post->id);
    }

    /** Danh sách tab kèm số lượng */
    public function getTabs(): array
    {
        $counts = $this->repository->countByStatus();

        $tabs = [['key' => 'all', 'label' => 'Tất cả', 'count' => array_sum($counts)]];

        foreach (PostStatus::cases() as $status) {
            $tabs[] = ['key' => $status->value, 'label' => $status->label(), 'count' => $counts[$status->value] ?? 0];
        }

        return $tabs;
    }

    /** Lưu bản dịch, đảm bảo slug duy nhất */
    private function saveTranslations(Post $post, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) {
                continue;
            }

            $data['slug'] = $this->generateUniqueSlug(
                translationTable: 'post_translations',
                locale: $locale,
                slug: $data['slug'] ?? null,
                title: $data['title'],
                ignoreForeignId: $post->id,
                foreignKey: 'post_id'
            );
            
            // Extract SEO fields to separate array
            $seoData = [
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'locale' => $locale,
            ];
            
            unset($data['meta_title'], $data['meta_description'], $data['meta_keywords']);

            $post->translations()->updateOrCreate(['locale' => $locale], $data);
        }
        
        // Luu SEO data
        $post->saveSeoTranslations($translations);
    }
}
