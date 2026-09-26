<?php

namespace App\Http\Requests\Admin\Product;

use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseProductRequest extends FormRequest
{
    use AuthorizesWithPermission;

    abstract protected function permission(): ?string;

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVariantSkus($validator, $this->ignoredVariantSkus());
            $this->validateAttributeOwnership($validator);
        });
    }

    /**
     * SKU biến thể phải duy nhất trong cả request lẫn bảng product_variants
     * (DB có unique constraint, nhưng validate trước để trả lỗi thân thiện).
     *
     * `$ignoreSkus` = các SKU biến thể đang thuộc về product này (update) —
     * submit lại form edit không đổi SKU không được báo trùng.
     */
    protected function validateVariantSkus($validator, array $ignoreSkus = []): void
    {
        $ignoreSkus = array_filter($ignoreSkus, fn ($sku) => ! empty($sku));

        $skus = [];
        foreach ((array) $this->input('variants', []) as $key => $variant) {
            $sku = $variant['sku'] ?? null;
            if (empty($sku)) {
                continue;
            }

            if (in_array($sku, $skus, true)) {
                $validator->errors()->add("variants.{$key}.sku", __('Mã SKU biến thể ":sku" bị trùng.', ['sku' => $sku]));

                continue;
            }

            $skus[] = $sku;

            if (in_array($sku, $ignoreSkus, true)) {
                continue;
            }

            if (ProductVariant::where('sku', $sku)->exists()) {
                $validator->errors()->add("variants.{$key}.sku", __('Mã SKU biến thể ":sku" đã tồn tại.', ['sku' => $sku]));
            }
        }
    }

    /**
     * Ràng buộc sở hữu thuộc tính:
     * - attribute phải là catalog toàn cục (product_id IS NULL) hoặc thuộc về
     *   chính product đang sửa (update).
     * - value_ids phải thuộc về attribute mà nó được gắn vào.
     *
     * Ngăn product B attach custom attribute / value của product A.
     */
    protected function validateAttributeOwnership($validator): void
    {
        $productId = $this->resolveProductId();

        $matrix = (array) $this->input('attributes', []);
        if (empty($matrix)) {
            return;
        }

        $attributeIds = [];
        foreach ($matrix as $index => $attribute) {
            $attributeId = (int) ($attribute['attribute_id'] ?? 0);
            if ($attributeId <= 0) {
                continue;
            }

            $attributeIds[$index] = $attributeId;
        }

        if (empty($attributeIds)) {
            return;
        }

        $allowedAttributeIds = ProductAttribute::whereIn('id', array_values($attributeIds))
            ->get()
            ->filter(fn ($attribute) => $attribute->product_id === null || $attribute->product_id === $productId)
            ->pluck('id')
            ->all();

        foreach ($attributeIds as $index => $attributeId) {
            if (! in_array($attributeId, $allowedAttributeIds, true)) {
                $validator->errors()->add(
                    "attributes.{$index}.attribute_id",
                    __('Thuộc tính không hợp lệ cho sản phẩm này.')
                );
            }
        }

        // value_ids phải thuộc đúng attribute chứa nó
        foreach ($matrix as $index => $attribute) {
            $attributeId = $attributeIds[$index] ?? 0;
            $valueIds = array_filter(array_map('intval', (array) ($attribute['value_ids'] ?? [])));

            if (empty($valueIds) || ! in_array($attributeId, $allowedAttributeIds, true)) {
                continue;
            }

            $validCount = ProductAttributeValue::whereIn('id', $valueIds)
                ->where('attribute_id', $attributeId)
                ->count();

            if ($validCount !== count($valueIds)) {
                $validator->errors()->add(
                    "attributes.{$index}.value_ids",
                    __('Giá trị thuộc tính không hợp lệ cho sản phẩm này.')
                );
            }
        }
    }

    /**
     * Product đang sửa (update) hoặc null (create).
     */
    protected function resolveProduct(): ?Product
    {
        $uuid = $this->route('uuid');

        if (empty($uuid)) {
            return null;
        }

        return Product::where('uuid', $uuid)->first();
    }

    protected function resolveProductId(): ?int
    {
        return $this->resolveProduct()?->id;
    }

    /**
     * SKU biến thể hiện có của product — được phép giữ nguyên khi update.
     */
    protected function ignoredVariantSkus(): array
    {
        $product = $this->resolveProduct();

        if (! $product) {
            return [];
        }

        return $product->variants()->pluck('sku')->all();
    }

    protected function defaultLocale(): string
    {
        return Language::where('is_default', true)->value('code') ?? app()->getLocale();
    }

    protected function sharedRules(): array
    {
        $defaultLocale = $this->defaultLocale();

        return [
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'status' => ['required', 'string', Rule::in(['published', 'draft', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'published_at' => ['nullable', 'date'],
            'translations' => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string'],
            'translations.*.description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*.image_uuid' => ['nullable', 'string', 'max:255'],
            'images.*.image_remove' => ['nullable', 'boolean'],
            'primary_image_uuid' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.is_variation' => ['nullable', 'boolean'],
            'attributes.*.value_ids' => ['nullable', 'array'],
            'attributes.*.value_ids.*' => ['integer', 'exists:product_attribute_values,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function sharedMessages(): array
    {
        return [
            'price.required' => __('Vui lòng nhập giá bán sản phẩm.'),
            'price.numeric' => __('Giá bán phải là định dạng số.'),
            'sku.unique' => __('Mã SKU này đã tồn tại trên hệ thống.'),
            'translations.*.name.required' => __('Tên sản phẩm không được để trống.'),
            'attributes.*.attribute_id.exists' => __('Thuộc tính không tồn tại.'),
            'attributes.*.value_ids.*.exists' => __('Giá trị thuộc tính không tồn tại.'),
        ];
    }
}
