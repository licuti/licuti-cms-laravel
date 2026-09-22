<?php

namespace App\Http\Requests\Admin\Product;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'products.create';
    }

    public function rules(): array
    {
        $defaultLocale = \App\Models\Language::where('is_default', true)->value('code') ?? app()->getLocale();

        return [
            'sku'             => ['nullable', 'string', 'max:100', 'unique:products,sku'],
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
            'images'          => ['nullable', 'array'],
            'images.*.image'  => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => __('Vui lòng nhập giá bán sản phẩm.'),
            'price.numeric'  => __('Giá bán phải là định dạng số.'),
            'sku.unique'     => __('Mã SKU này đã tồn tại trên hệ thống.'),
            'translations.*.name.required' => __('Tên sản phẩm không được để trống.'),
        ];
    }
}