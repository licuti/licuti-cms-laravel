<?php

namespace App\Services\Admin\Product;

use App\Core\Base\BaseService;
use App\DTOs\Product\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    public function create(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            if (empty($data['sku'])) {
                $data['sku'] = 'PRD-' . strtoupper(Str::random(8));
            }

            $model = $this->repository->create($data);

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['name'])) {
                    $transData['slug'] = $this->generateUniqueSlug(
                        translationTable: 'product_translations',
                        locale: $locale,
                        slug: $transData['slug'] ?? null,
                        title: $transData['name'],
                        ignoreForeignId: $model->id,
                        foreignKey: 'product_id'
                    );

                    $model->translations()->create([
                        'locale'            => $locale,
                        'name'              => $transData['name'],
                        'slug'              => $transData['slug'],
                        'short_description' => $transData['short_description'] ?? null,
                        'description'       => $transData['description'] ?? null,
                    ]);
                }
            }

            // SEO Metadata
            if (method_exists($model, 'saveSeoTranslations')) {
                $model->saveSeoTranslations($dto->translations);
            }

            // Images
            foreach ($dto->images as $index => $imageData) {
                if (!empty($imageData['image'])) {
                    $model->images()->create([
                        'image'         => $imageData['image'],
                        'is_primary'    => !empty($imageData['is_primary']),
                        'display_order' => $index,
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['name'])) {
                    $transData['slug'] = $this->generateUniqueSlug(
                        translationTable: 'product_translations',
                        locale: $locale,
                        slug: $transData['slug'] ?? null,
                        title: $transData['name'],
                        ignoreForeignId: $model->id,
                        foreignKey: 'product_id'
                    );

                    $model->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'name'              => $transData['name'],
                            'slug'              => $transData['slug'],
                            'short_description' => $transData['short_description'] ?? null,
                            'description'       => $transData['description'] ?? null,
                        ]
                    );
                }
            }

            // SEO Metadata
            if (method_exists($model, 'saveSeoTranslations')) {
                $model->saveSeoTranslations($dto->translations);
            }

            // Images sync
            if (!empty($dto->images)) {
                $model->images()->delete();
                foreach ($dto->images as $index => $imageData) {
                    if (!empty($imageData['image'])) {
                        $model->images()->create([
                            'image'         => $imageData['image'],
                            'is_primary'    => !empty($imageData['is_primary']) || $index === 0,
                            'display_order' => $index,
                        ]);
                    }
                }
            }

            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            return $this->repository->delete($model->id);
        });
    }
}
