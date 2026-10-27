<?php

namespace App\Http\Requests\Api\Cart;

use App\Core\Base\BaseRequest;

class AddCartItemRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxPerLine = config('cart.max_quantity_per_line', 999);

        return [
            'product_uuid' => ['required', 'string', 'exists:products,uuid'],
            'variant_uuid' => ['nullable', 'string', 'exists:product_variants,uuid'],
            'quantity'     => ['required', 'integer', 'min:1', "max:{$maxPerLine}"],
            'meta'         => ['nullable', 'array'],
        ];
    }
}
