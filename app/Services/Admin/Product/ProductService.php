<?php

namespace App\Services\Admin\Product;

use App\Core\Base\BaseService;
use App\Core\Enums\ContentStatus;
use App\DTOs\Product\ProductDTO;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Shared\Language\LanguageResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly ProductAttributeRepositoryInterface $attributeRepository,
        private readonly TagRepositoryInterface $tagRepository,
        private readonly LanguageResolver $languageResolver
    ) {}

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    /**
     * Đếm sản phẩm theo tab trạng thái (all | published | draft | archived).
     */
    public function getTabs(): array
    {
        $counts = $this->repository->countByStatus();

        $tabs = collect(ContentStatus::cases())
            ->map(fn ($status) => [
                'key' => $status->value,
                'label' => $status->label(),
                'count' => $counts[$status->value] ?? 0,
            ])
            ->all();

        return array_merge([
            ['key' => 'all', 'label' => __('Tất cả'), 'count' => array_sum($counts)],
        ], $tabs);
    }

    /**
     * Đổi trạng thái nhiều sản phẩm trong 1 query (bulk action).
     */
    public function updateStatusByIds(array $ids, string $status): int
    {
        return $this->handleTransaction(fn () => $this->repository->updateStatusByIds($ids, $status));
    }

    public function create(ProductDTO $dto): Product
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();

            if (empty($data['sku'])) {
                $data['sku'] = config('products.sku_prefix').strtoupper(Str::random(8));
            }

            $model = $this->repository->create($data);

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (! empty($transData['name'])) {
                    $transData['slug'] = $this->generateUniqueSlug(
                        translationTable: 'product_translations',
                        locale: $locale,
                        slug: $transData['slug'] ?? null,
                        title: $transData['name'],
                        ignoreForeignId: $model->id,
                        foreignKey: 'product_id'
                    );

                    $model->translations()->create([
                        'locale' => $locale,
                        'name' => $transData['name'],
                        'slug' => $transData['slug'],
                        'short_description' => $transData['short_description'] ?? null,
                        'description' => $transData['description'] ?? null,
                    ]);
                }
            }

            // SEO Metadata
            if (method_exists($model, 'saveSeoTranslations')) {
                $model->saveSeoTranslations($dto->translations);
            }

            // Images
            foreach ($dto->images as $index => $imageData) {
                if (! empty($imageData['image'])) {
                    $model->images()->create([
                        'image' => $imageData['image'],
                        'display_order' => $index,
                    ]);
                }
            }

            // Attributes & Variants
            $matrix = $this->syncAttributes($model, $dto->attributes);
            $matrix = $this->createNewAttributes($model, $dto->newAttributes, $matrix);
            $this->generateVariants($model, $matrix, $dto->variants);

            $this->syncTags($model, $dto->tagIds);

            return $model;
        });
    }

    public function update(string $uuid, ProductDTO $dto): Product
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            // Translations
            foreach ($dto->translations as $locale => $transData) {
                if (! empty($transData['name'])) {
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
                            'name' => $transData['name'],
                            'slug' => $transData['slug'],
                            'short_description' => $transData['short_description'] ?? null,
                            'description' => $transData['description'] ?? null,
                        ]
                    );
                }
            }

            // SEO Metadata
            if (method_exists($model, 'saveSeoTranslations')) {
                $model->saveSeoTranslations($dto->translations);
            }

            // Images sync
            if (! empty($dto->images)) {
                $model->images()->delete();
                foreach ($dto->images as $index => $imageData) {
                    if (! empty($imageData['image'])) {
                        $model->images()->create([
                            'image' => $imageData['image'],
                            'display_order' => $index,
                        ]);
                    }
                }
            }

            // Attributes & Variants
            $matrix = $this->syncAttributes($model, $dto->attributes);
            $matrix = $this->createNewAttributes($model, $dto->newAttributes, $matrix);
            $this->generateVariants($model, $matrix, $dto->variants);

            $this->syncTags($model, $dto->tagIds);

            return $model;
        });
    }

    /**
     * Sync tags cho product.
     *
     * `tagData` = ['ids' => [int], 'text' => [string]] (do DTO parse).
     * Tag text mới được tạo (slug sinh từ tên, trùng slug thì dùng tag
     * hiện có). Chạy trong transaction của create/update.
     */
    public function syncTags(Product $product, array $tagData): void
    {
        $ids = array_values(array_unique(array_map('intval', (array) ($tagData['ids'] ?? []))));

        foreach ((array) ($tagData['text'] ?? []) as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);
            $existing = $this->tagRepository->findWhere(['slug' => $slug])->first();

            if ($existing) {
                $ids[] = $existing->id;

                continue;
            }

            $tag = $this->tagRepository->create([
                'name' => $name,
                'slug' => $slug,
            ]);

            $ids[] = $tag->id;
        }

        $product->tags()->sync(array_values(array_unique(array_filter($ids, fn ($id) => $id > 0))));
    }

    /**
     * Sync pivot product_attribute + product_attribute_value.
     *
     * `$attributeMatrix` = [['attribute_id' => int, 'is_variation' => bool,
     * 'value_ids' => [int], 'new_values' => [string]]]. CascadeOnDelete ở
     * pivot product_attribute_value tự xóa link variant↔value không còn hợp lệ.
     *
     * Giá trị mới (text từ tom-select create) được tạo tại đây, id đưa thẳng
     * vào value_ids. Trả về matrix đã resolve để generateVariants dùng.
     */
    public function syncAttributes(Product $product, array $attributeMatrix): array
    {
        $matrix = array_values(array_filter($attributeMatrix, fn ($a) => ! empty($a['attribute_id'])));

        // Tạo giá trị mới (text) → đưa id vào value_ids
        foreach ($matrix as &$attribute) {
            $attributeId = (int) $attribute['attribute_id'];
            $newValues = array_values(array_unique((array) ($attribute['new_values'] ?? [])));

            foreach ($newValues as $text) {
                $existing = $this->attributeRepository->findValueByText($attributeId, $text);

                if ($existing) {
                    $attribute['value_ids'][] = $existing->id;

                    continue;
                }

                $created = $this->attributeRepository->createValue($attributeId, $text);

                $attribute['value_ids'][] = $created->id;
            }

            $attribute['value_ids'] = array_values(array_unique(array_map('intval', $attribute['value_ids'])));
        }
        unset($attribute);

        $sync = [];
        foreach ($matrix as $order => $attribute) {
            $attributeId = (int) $attribute['attribute_id'];
            if (isset($sync[$attributeId])) {
                continue;
            }
            $sync[$attributeId] = [
                'is_variation' => ! empty($attribute['is_variation']),
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

        return $matrix;
    }

    /**
     * Tạo thuộc tính tùy chỉnh từ new_attributes[] (submit cùng form chính).
     *
     * Chỉ chạy được khi product đã save (cần product_id để scope). Trả về
     * matrix có thêm entry của attribute mới (kèm value_ids thật) để
     * generateVariants sinh tổ hợp được luôn.
     */
    public function createNewAttributes(Product $product, array $newAttributes, array $matrix = []): array
    {
        $defaultLocale = $this->languageResolver->getDefaultLanguage()?->code ?? app()->getLocale();

        $order = count($matrix);

        foreach ($newAttributes as $newAttribute) {
            $code = 'attr_'.Str::lower(Str::random(8));

            $attribute = $this->attributeRepository->create([
                'product_id' => $product->id,
                'code' => $code,
                'type' => $newAttribute['type'] ?? 'select',
            ]);

            $attribute->translations()->create([
                'locale' => $defaultLocale,
                'name' => $newAttribute['name'],
            ]);

            $valueIds = [];
            foreach ($newAttribute['values'] as $text) {
                $value = $this->attributeRepository->createValue($attribute->id, $text);
                $valueIds[] = $value->id;
            }

            // Sync pivot cho attribute mới
            $product->attributes()->attach($attribute->id, [
                'is_variation' => ! empty($newAttribute['is_variation']),
                'display_order' => $order++,
            ]);

            $product->attributeValues()->syncWithoutDetaching($valueIds);

            $matrix[] = [
                'attribute_id' => $attribute->id,
                'is_variation' => ! empty($newAttribute['is_variation']),
                'value_ids' => $valueIds,
            ];
        }

        return $matrix;
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
            fn ($a) => ! empty($a['is_variation']) && ! empty($a['value_ids'])
        ));

        if (empty($variationAttributes)) {
            $product->variants()->delete();

            return;
        }

        $maxCombos = (int) config('products.max_combos', 100);

        $comboCount = 1;
        foreach ($variationAttributes as $attribute) {
            $comboCount *= count($attribute['value_ids']);
        }

        if ($comboCount > $maxCombos) {
            throw ValidationException::withMessages([
                'attributes' => __('Tổ hợp biến thể quá lớn (:count). Tối đa :max tổ hợp được phép.', [
                    'count' => $comboCount,
                    'max' => $maxCombos,
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

        // Combo key phải được sort numeric tăng dần để khớp với phía form
        // (JS `combo.sort()`) và phía existingByKey (collection `sort()`).
        // Không sort sẽ trượt key khi id value của 2 attribute đan xen.
        $comboKeys = array_map(function ($combo) {
            $ids = array_map('intval', $combo);
            sort($ids, SORT_NUMERIC);

            return implode('-', $ids);
        }, $combos);

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
                    'sku' => ! empty($data['sku']) ? $data['sku'] : $variant->sku,
                    'barcode' => ! empty($data['barcode']) ? $data['barcode'] : $variant->barcode,
                    'price' => isset($data['price']) ? $data['price'] : $variant->price,
                    'compare_price' => isset($data['compare_price']) ? $data['compare_price'] : $variant->compare_price,
                    'cost_price' => isset($data['cost_price']) ? $data['cost_price'] : $variant->cost_price,
                    'stock_quantity' => isset($data['stock_quantity']) ? $data['stock_quantity'] : $variant->stock_quantity,
                    'image' => ! empty($data['image']) ? $data['image'] : $variant->image,
                    'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $variant->is_active,
                    'display_order' => $order,
                ]);
                $keepIds[$variant->id] = $variant->id;

                continue;
            }

            $variant = $product->variants()->create([
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                // Cột price NOT NULL trong DB — variant mới kế thừa giá product
                'price' => $data['price'] ?? $product->price,
                'compare_price' => $data['compare_price'] ?? null,
                'cost_price' => $data['cost_price'] ?? null,
                'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
                'image' => $data['image'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                'display_order' => $order,
            ]);

            $variant->attributeValues()->sync($combos[$order]);
            $keepIds[$variant->id] = $variant->id;
        }

        $product->variants()->whereNotIn('id', array_values($keepIds))->delete();
    }

    public function delete(string $uuid): bool
    {
        return $this->handleTransaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            return $this->repository->delete($model->id);
        });
    }

    /**
     * Tạo thuộc tính custom (product_id = product) từ form sản phẩm.
     */
    public function createCustomAttribute(string $productUuid, array $data): ProductAttribute
    {
        return $this->handleTransaction(function () use ($productUuid, $data) {
            $product = $this->repository->findByUuidWithRelations($productUuid);

            $code = $data['code'] ?? null;
            if (empty($code)) {
                $code = 'attr_'.Str::lower(Str::random(8));
            }

            $attribute = $this->attributeRepository->create([
                'product_id' => $product->id,
                'code' => $code,
                'type' => $data['type'] ?? 'select',
            ]);

            foreach ($data['translations'] ?? [] as $locale => $transData) {
                if (! empty($transData['name'])) {
                    $attribute->translations()->create([
                        'locale' => $locale,
                        'name' => $transData['name'],
                    ]);
                }
            }

            foreach ($data['values'] ?? [] as $index => $valItem) {
                if (! empty($valItem['value'])) {
                    $attribute->values()->create([
                        'value' => $valItem['value'],
                        'color_code' => $valItem['color_code'] ?? null,
                        'display_order' => $valItem['display_order'] ?? $index,
                    ]);
                }
            }

            return $attribute->fresh(['translations', 'values']);
        });
    }
}
