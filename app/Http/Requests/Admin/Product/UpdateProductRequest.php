<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends BaseProductRequest
{
    protected function permission(): ?string
    {
        return 'products.update';
    }

    public function rules(): array
    {
        $productId = $this->resolveProductId();

        $rules = $this->sharedRules();
        $rules['sku'][] = Rule::unique('products', 'sku')->ignore($productId);

        return $rules;
    }

    public function messages(): array
    {
        return $this->sharedMessages();
    }
}
