<?php

namespace App\Http\Requests\Admin\Product;

class StoreProductRequest extends BaseProductRequest
{
    protected function permission(): ?string
    {
        return 'products.create';
    }

    public function rules(): array
    {
        $rules = $this->sharedRules();
        $rules['sku'][] = 'unique:products,sku';

        return $rules;
    }

    public function messages(): array
    {
        return $this->sharedMessages();
    }
}
