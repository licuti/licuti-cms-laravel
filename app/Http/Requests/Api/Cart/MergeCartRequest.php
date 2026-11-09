<?php

namespace App\Http\Requests\Api\Cart;

use App\Core\Base\BaseRequest;

class MergeCartRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'guest_cart_token' => ['required', 'string', 'exists:carts,uuid'],
        ];
    }
}
