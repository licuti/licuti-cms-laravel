<?php

namespace App\Services\Admin\ContentBlock;

use App\Core\Base\BaseService;
use App\Core\Enums\ContentStatus;
use App\DTOs\ContentBlock\ContentBlockDTO;
use App\Models\ContentBlock;
use App\Repositories\Interfaces\ContentBlockRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentBlockService extends BaseService
{
    public function __construct(
        private readonly ContentBlockRepositoryInterface $repository,
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {}

    public function getStatusOptions(): array
    {
        return collect(ContentStatus::cases())
            ->mapWithKeys(fn (ContentStatus $status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters);
    }

    public function getTabs(): array
    {
        $counts = $this->repository->countByStatus();
        $tabs = [['key' => 'all', 'label' => __('Tất cả'), 'count' => array_sum($counts)]];

        foreach (ContentStatus::cases() as $status) {
            $tabs[] = [
                'key' => $status->value,
                'label' => $status->label(),
                'count' => $counts[$status->value] ?? 0,
            ];
        }

        return $tabs;
    }

    public function getFormOptions(?ContentBlock $block = null, ?string $requestedLocale = null): array
    {
        $languages = $this->languageRepository->getActiveLanguages();
        $defaultLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();
        $defaultLocale = $defaultLanguage?->code ?? app()->getLocale();
        $currentLocale = $block
            && is_string($requestedLocale)
            && $languages->contains('code', $requestedLocale)
                ? $requestedLocale
                : $defaultLocale;

        return [
            'statuses' => $this->getStatusOptions(),
            'activeLanguages' => $languages,
            'languages' => $languages,
            'defaultLocale' => $defaultLocale,
            'currentLocale' => $currentLocale,
        ];
    }

    public function findByUuid(string $uuid): ContentBlock
    {
        return $this->repository->findByUuid($uuid, ['*'], ['translations', 'imageMedia']);
    }

    public function create(ContentBlockDTO $dto): ContentBlock
    {
        return $this->handleTransaction(function () use ($dto) {
            $block = $this->repository->create($dto->toArray());
            $this->saveTranslations($block, $dto->translations);

            return $block->load(['translations', 'imageMedia']);
        });
    }

    public function update(string $uuid, ContentBlockDTO $dto): ContentBlock
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $block = $this->repository->findByUuid($uuid);
            $this->repository->update($block->id, $dto->toArray());
            $this->saveTranslations($block, $dto->translations);

            return $block->fresh(['translations', 'imageMedia']);
        });
    }

    public function delete(string $uuid): bool
    {
        $block = $this->repository->findByUuid($uuid);

        return $this->repository->delete($block->id);
    }

    private function saveTranslations(ContentBlock $block, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) {
                continue;
            }

            $slug = $this->generateUniqueSlug(
                translationTable: 'content_block_translations',
                locale: (string) $locale,
                slug: $data['slug'] ?? null,
                title: $data['title'],
                ignoreForeignId: $block->id,
                foreignKey: 'content_block_id',
            );

            $data['slug'] = $slug;
            $block->translations()->updateOrCreate(['locale' => $locale], $data);
        }
    }
}
