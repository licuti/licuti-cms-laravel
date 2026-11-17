<?php

namespace App\Services\Admin\Post;

use App\Core\Base\BaseService;
use App\Core\Enums\ContentStatus;
use App\DTOs\Post\PostDTO;
use App\Models\Post;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService extends BaseService
{
    public function __construct(
        private readonly PostRepositoryInterface $repository,
        private readonly PostCategoryRepositoryInterface $categoryRepository,
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly UserRepositoryInterface $userRepository
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

    /** Chi tiết bài viết cho trang sửa (kèm quan hệ cần cho form) */
    public function findByUuid(string $uuid): Post
    {
        return $this->repository->findByUuid($uuid, ['*'], ['translations', 'imageMedia', 'categories']);
    }

    /** Danh mục đang active cho bộ lọc ở trang danh sách */
    public function getActiveCategories()
    {
        return $this->categoryRepository->getAllActive();
    }

    /**
     * Dữ liệu dùng chung cho create/edit form:
     * status, danh mục dạng cây, tác giả, ngôn ngữ và locale đang soạn.
     */
    public function getFormOptions(?Post $post = null, ?string $requestedLocale = null): array
    {
        $activeLanguages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $activeLanguages->firstWhere('is_default', true) ?? $activeLanguages->first();
        $defaultLocale = $defaultLanguage?->code ?? app()->getLocale();

        // Bài viết mới luôn soạn bằng ngôn ngữ mặc định; bản dịch thêm sau khi đã lưu.
        $currentLocale = $post
            && is_string($requestedLocale)
            && $activeLanguages->contains('code', $requestedLocale)
                ? $requestedLocale
                : $defaultLocale;

        return [
            'statuses'        => $this->getStatusOptions(),
            'categories'      => $this->categoryRepository->getTree(),
            'authors'         => $this->userRepository->getActiveAuthors(),
            'activeLanguages' => $activeLanguages,
            'languages'       => $activeLanguages,
            'defaultLocale'   => $defaultLocale,
            'currentLocale'   => $currentLocale,
        ];
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