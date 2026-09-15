<?php

namespace App\Services\Admin\Page;

use App\Core\Base\BaseService;
use App\DTOs\Page\PageDTO;
use App\Models\Page;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PageService extends BaseService
{
    public function __construct(
        private readonly PageRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    public function findByUuid(string $uuid): Page
    {
        return $this->repository->findByUuid($uuid);
    }

    public function create(PageDTO $dto): Page
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['title'])) {
                    continue;
                }

                $transData['slug'] = $this->generateUniqueSlug(
                    translationTable: 'page_translations',
                    locale: $locale,
                    slug: $transData['slug'] ?? null,
                    title: $transData['title'],
                    ignoreForeignId: $model->id,
                    foreignKey: 'page_id'
                );

                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);
                $model->translations()->create(array_merge($transData, ['locale' => $locale]));
            }
            
            $model->saveSeoTranslations($dto->translations);
            
            return $model;
        });
    }

    public function update(string $uuid, PageDTO $dto): Page
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuid($uuid);
            
            $this->repository->update($model->id, $dto->toArray());
            foreach ($dto->translations as $locale => $transData) {
                if (empty($transData['title'])) {
                    continue;
                }

                $transData['slug'] = $this->generateUniqueSlug(
                    translationTable: 'page_translations',
                    locale: $locale,
                    slug: $transData['slug'] ?? null,
                    title: $transData['title'],
                    ignoreForeignId: $model->id,
                    foreignKey: 'page_id'
                );

                unset($transData['meta_title'], $transData['meta_description'], $transData['meta_keywords']);
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
        $model = $this->repository->findByUuid($uuid);
        return $this->repository->delete($model->id);
    }
}
