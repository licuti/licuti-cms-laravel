<?php

namespace App\Http\Requests\Api\Cart;

use App\Core\Base\BaseRequest;

class ApplyCouponRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:100'],
            'guest_email' => ['nullable', 'email', 'max:100'],
        ];
    }
}
