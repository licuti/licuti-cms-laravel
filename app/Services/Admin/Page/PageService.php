<?php

namespace App\Services\Admin\Page;

use App\Core\Base\BaseService;
use App\Core\Enums\ContentStatus;
use App\DTOs\Page\PageDTO;
use App\Models\Page;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PageService extends BaseService
{
    public function __construct(
        private readonly PageRepositoryInterface $repository
    ) {}

    /**
     * Danh sách status kèm label tiếng Việt (cache trong request).
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

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters);
    }

    public function findByUuid(string $uuid): Page
    {
        return $this->repository->findByUuid($uuid, ['*'], ['translations', 'imageMedia']);
    }

    public function create(PageDTO $dto): Page
    {
        return $this->handleTransaction(function () use ($dto) {
            $model = $this->repository->create($dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['title'])) {
                    continue;
                }
                $this->saveTranslation($model, (string) $locale, $transData);
            }

            $model->saveSeoTranslations($dto->translations);

            return $model->load('translations');
        });
    }

    public function update(string $uuid, PageDTO $dto): Page
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            $this->assertParentSafe($model->id, $dto->parentId);

            $this->repository->update($model->id, $dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['title'])) {
                    continue;
                }
                $this->saveTranslation($model, (string) $locale, $transData);
            }

            $model->saveSeoTranslations($dto->translations);

            return $model->fresh(['translations']);
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
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

    /** Danh sách trang (bỏ current page + subtree của nó) để chọn trang cha */
    public function getParentOptions(?int $excludeId = null)
    {
        return $this->repository->getTree($excludeId);
    }

    /**
     * Lưu 1 bản dịch:
     * - Tách các trường SEO ra ngoài → gọi saveSeoTranslations xử lý
     * - Luôn gọi generateUniqueSlug để đảm bảo slug duy nhất + chống trùng
     */
    private function saveTranslation(Page $model, string $locale, array $data): void
    {
        // Tách SEO fields trước khi tạo slug (không có trong translation)
        $seoMetaKeys = ['meta_title', 'meta_description', 'meta_keywords'];
        unset($data[$seoMetaKeys[0]], $data[$seoMetaKeys[1]], $data[$seoMetaKeys[2]]);

        // Slug
        $data['slug'] = $this->generateUniqueSlug(
            translationTable: 'page_translations',
            locale: $locale,
            slug: $data['slug'] ?? null,
            title: $data['title'] ?? '',
            ignoreForeignId: $model->id,
            foreignKey: 'page_id'
        );

        $model->translations()->updateOrCreate(['locale' => $locale], $data);
    }

    /**
     * Kiểm tra vòng lặp khi đổi trang cha: parent không được là chính nó,
     * cũng không được là một node trong subtree của trang hiện tại.
     */
    private function assertParentSafe(?int $selfId, ?int $targetParentId): void
    {
        if (!$selfId || !$targetParentId) {
            return;
        }
        if ($selfId === $targetParentId) {
            abort(422, __('Trang không thể là trang cha của chính nó.'));
        }

        // Walk up from target parent
        $current = $targetParentId;
        $depth = 0;
        while ($current && $depth++ < 50) {
            $node = Page::query()->find($current, ['id', 'parent_id']);
            if (!$node) {
                break;
            }
            if ((int) $node->id === $selfId) {
                abort(422, __('Không thể gán trang là cha của một trang trong hệ thống con của nó.'));
            }
            $current = $node->parent_id;
        }
    }
}
