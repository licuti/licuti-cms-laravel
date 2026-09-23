<?php

namespace App\Services\Admin\Product;

use App\Core\Base\BaseService;
use App\DTOs\Product\ProductDTO;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly ProductAttributeRepositoryInterface $attributeRepository
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

            // Attributes & Variants
            $this->syncAttributes($model, $dto->attributes);
            $this->generateVariants($model, $dto->attributes, $dto->variants);

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

            // Attributes & Variants
            $this->syncAttributes($model, $dto->attributes);
            $this->generateVariants($model, $dto->attributes, $dto->variants);

            return $model;
        });
    }

    /**
     * Sync pivot product_attribute + product_attribute_value.
     *
     * `$attributeMatrix` = [['attribute_id' => int, 'is_variation' => bool,
     * 'value_ids' => [int]]]. CascadeOnDelete ở pivot product_attribute_value tự
     * xóa link variant↔value không còn hợp lệ.
     */
    public function syncAttributes(Product $product, array $attributeMatrix): void
    {
        $matrix = array_values(array_filter($attributeMatrix, fn ($a) => !empty($a['attribute_id'])));

        $sync = [];
        foreach ($matrix as $order => $attribute) {
            $attributeId = (int) $attribute['attribute_id'];
            if (isset($sync[$attributeId])) {
                continue;
            }
            $sync[$attributeId] = [
                'is_variation'  => !empty($attribute['is_variation']),
                'display_order' => $order,
            ];
        }

        $product->attributes()->sync($sync);

        $valueIds = [];
        foreach ($matrix as $attribute) {
            foreach ((array) ($attribute['value_ids'] ?? []) as $valueId) {
                $valueIds[(int) $valueId] = (int) $valueId;
            }
        }

        $product->attributeValues()->sync(array_values($valueIds));
    }

    /**
     * Sinh biến thể từ tổ hợp cartesian các attribute is_variation.
     *
     * Preserve-by-combo: variant cũ khớp key giữ nguyên sku/price/stock; tổ hợp
     * mới tạo variant mới (mặc định theo product); tổ hợp bị bỏ xóa. Guard nổ
     * tổ hợp: > 100 combos throw ValidationException.
     */
    public function generateVariants(Product $product, array $attributeMatrix, array $variantData = []): void
    {
        $variationAttributes = array_values(array_filter(
            $attributeMatrix,
            fn ($a) => !empty($a['is_variation']) && !empty($a['value_ids'])
        ));

        if (empty($variationAttributes)) {
            $product->variants()->delete();
            return;
        }

        $maxCombos = 100;

        $comboCount = 1;
        foreach ($variationAttributes as $attribute) {
            $comboCount *= count($attribute['value_ids']);
        }

        if ($comboCount > $maxCombos) {
            throw ValidationException::withMessages([
                'attributes' => __('Tổ hợp biến thể quá lớn (:count). Tối đa :max tổ hợp được phép.', [
                    'count' => $comboCount,
                    'max'   => $maxCombos,
                ]),
            ]);
        }

        $combos = [[]];
        foreach ($variationAttributes as $attribute) {
            $valueIds = array_values(array_unique(array_map('intval', $attribute['value_ids'])));
            $next = [];
            foreach ($combos as $partial) {
                foreach ($valueIds as $valueId) {
                    $next[] = array_merge($partial, [$valueId]);
                }
            }
            $combos = $next;
        }

        $comboKeys = array_map(fn ($combo) => implode('-', $combo), $combos);

        $existing = $product->variants()
            ->with('attributeValues')
            ->get();

        $existingByKey = [];
        foreach ($existing as $variant) {
            $key = $variant->attributeValues->pluck('id')->sort()->implode('-');
            $existingByKey[$key] = $variant;
        }

        $keepIds = [];

        foreach ($comboKeys as $order => $key) {
            $data = $variantData[$key] ?? [];

            if (isset($existingByKey[$key])) {
                $variant = $existingByKey[$key];
                $variant->update([
                    'sku'            => !empty($data['sku']) ? $data['sku'] : $variant->sku,
                    'price'          => isset($data['price']) ? $data['price'] : $variant->price,
                    'compare_price'  => isset($data['compare_price']) ? $data['compare_price'] : $variant->compare_price,
                    'stock_quantity' => isset($data['stock_quantity']) ? $data['stock_quantity'] : $variant->stock_quantity,
                    'is_active'      => isset($data['is_active']) ? (bool) $data['is_active'] : $variant->is_active,
                    'display_order'  => $order,
                ]);
                $keepIds[$variant->id] = $variant->id;
                continue;
            }

            $variant = $product->variants()->create([
                'uuid'           => Str::uuid()->toString(),
                'sku'            => $data['sku'] ?? null,
                'price'          => $data['price'] ?? null,
                'compare_price'  => $data['compare_price'] ?? null,
                'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
                'is_active'      => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                'display_order'  => $order,
            ]);

            $variant->attributeValues()->sync($combos[$order]);
            $keepIds[$variant->id] = $variant->id;
        }

        $product->variants()->whereNotIn('id', array_values($keepIds))->delete();
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            return $this->repository->delete($model->id);
        });
    }

    /**
     * Tạo thuộc tính custom (product_id = product) từ form sản phẩm.
     */
    public function createCustomAttribute(string $productUuid, array $data): ProductAttribute
    {
        return DB::transaction(function () use ($productUuid, $data) {
            $product = $this->repository->findByUuidWithRelations($productUuid);

            $code = $data['code'] ?? null;
            if (empty($code)) {
                $code = 'attr_' . Str::lower(Str::random(8));
            }

            $attribute = $this->attributeRepository->create([
                'uuid'       => Str::uuid()->toString(),
                'product_id' => $product->id,
                'code'       => $code,
                'type'       => $data['type'] ?? 'select',
            ]);

            foreach ($data['translations'] ?? [] as $locale => $transData) {
                if (!empty($transData['name'])) {
                    $attribute->translations()->create([
                        'locale' => $locale,
                        'name'   => $transData['name'],
                    ]);
                }
            }

            foreach ($data['values'] ?? [] as $index => $valItem) {
                if (!empty($valItem['value'])) {
                    $attribute->values()->create([
                        'uuid'          => Str::uuid()->toString(),
                        'value'         => $valItem['value'],
                        'color_code'    => $valItem['color_code'] ?? null,
                        'display_order' => $valItem['display_order'] ?? $index,
                    ]);
                }
            }

            return $attribute->fresh(['translations', 'values']);
        });
    }
}
