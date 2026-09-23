<?php

namespace App\Http\Requests\Admin\Product;

use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'products.update';
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVariantSkus($validator);
        });
    }

    /**
     * SKU biến thể phải duy nhất trong cả request lẫn bảng product_variants
     * (DB có unique constraint, nhưng validate trước để trả lỗi thân thiện).
     */
    protected function validateVariantSkus($validator): void
    {
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

            if (ProductVariant::where('sku', $sku)->exists()) {
                $validator->errors()->add("variants.{$key}.sku", __('Mã SKU biến thể ":sku" đã tồn tại.', ['sku' => $sku]));
            }
        }
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');
        $product = Product::where('uuid', $uuid)->first();
        $productId = $product?->id;

        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'sku'             => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode'         => ['nullable', 'string', 'max:100'],
            'price'           => ['required', 'numeric', 'min:0'],
            'compare_price'   => ['nullable', 'numeric', 'min:0'],
            'cost_price'      => ['nullable', 'numeric', 'min:0'],
            'stock_quantity'  => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'brand_id'        => ['nullable', 'exists:brands,id'],
            'status'          => ['required', 'string', Rule::in(['published', 'draft', 'archived'])],
            'is_featured'     => ['nullable', 'boolean'],
            'weight'          => ['nullable', 'numeric', 'min:0'],
            'dimensions'      => ['nullable', 'string', 'max:100'],
            'published_at'    => ['nullable', 'date'],
            'translations'    => ['required', 'array'],
            "translations.{$defaultLocale}.name" => ['required', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.short_description' => ['nullable', 'string'],
            'translations.*.description' => ['nullable', 'string'],
            'images'                => ['nullable', 'array'],
            'images.*.image_uuid'   => ['nullable', 'string', 'max:255'],
            'images.*.image_remove' => ['nullable', 'boolean'],
            'primary_index'         => ['nullable', 'integer', 'min:0'],
            'attributes'              => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.is_variation' => ['nullable', 'boolean'],
            'attributes.*.value_ids'    => ['nullable', 'array'],
            'attributes.*.value_ids.*'  => ['integer', 'exists:product_attribute_values,id'],
            'variants'                 => ['nullable', 'array'],
            'variants.*.sku'           => ['nullable', 'string', 'max:100'],
            'variants.*.price'         => ['nullable', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity'=> ['nullable', 'integer', 'min:0'],
            'variants.*.is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => __('Vui lòng nhập giá bán sản phẩm.'),
            'price.numeric'  => __('Giá bán phải là định dạng số.'),
            'sku.unique'     => __('Mã SKU này đã tồn tại trên hệ thống.'),
            'translations.*.name.required' => __('Tên sản phẩm không được để trống.'),
            'attributes.*.attribute_id.exists' => __('Thuộc tính không tồn tại.'),
            'attributes.*.value_ids.*.exists' => __('Giá trị thuộc tính không tồn tại.'),
        ];
    }
}