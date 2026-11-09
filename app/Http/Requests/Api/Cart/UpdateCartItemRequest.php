<?php

namespace App\Http\Requests\Api\Cart;

use App\Core\Base\BaseRequest;

class UpdateCartItemRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxPerLine = config('cart.max_quantity_per_line', 999);

        return [
            'quantity' => ['required', 'integer', 'min:0', "max:{$maxPerLine}"],
        ];
    }
}
