<?php

namespace App\Services\Admin\Post;

use App\Core\Base\BaseService;
use App\Core\Enums\ContentStatus;
use App\DTOs\Post\PostDTO;
use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService extends BaseService
{
    public function __construct(
        private readonly PostRepositoryInterface $repository
    ) {}

    /**
     * Danh sách status kèm label tiếng Việt.
     * Cache kết quả trong request để Controller không phải build lại 3 lần.
     */
    public function getStatusOptions(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = collect(ContentStatus::cases())
                ->mapWithKeys(fn(ContentStatus $s) => [$s->value => $s->label()])
                ->toArray();
        }

        return $cache;
    }

    /**
     * Mảng [value => label] của status, dùng cho BulkActionServiceProvider.
     * Thay thế cho constant STATUSES trước đây.
     */
    public function getStatusMap(): array
    {
        $map = [];
        foreach (ContentStatus::cases() as $status) {
            $map[$status->value] = $status->label();
        }
        return $map;
    }

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

        foreach (ContentStatus::cases() as $status) {
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

            unset($data['meta_title'], $data['meta_description'], $data['meta_keywords']);

            $post->translations()->updateOrCreate(['locale' => $locale], $data);
        }

        $post->saveSeoTranslations($translations);
    }
}