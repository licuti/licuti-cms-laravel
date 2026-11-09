<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_code'        => ['required', 'string', 'exists:payment_methods,code'],
            'recipient_name'             => ['required', 'string', 'max:100'],
            'phone'                      => ['required', 'string', 'max:20'],
            'email'                      => ['nullable', 'email', 'max:100'],
            'address_line'               => ['required', 'string', 'max:255'],
            'ward'                       => ['nullable', 'string', 'max:100'],
            'district'                   => ['nullable', 'string', 'max:100'],
            'province'                   => ['nullable', 'string', 'max:100'],
            'notes'                      => ['nullable', 'string', 'max:1000'],
            'cart_token'                 => ['nullable', 'string', 'exists:carts,uuid'],
            'coupon_code'                => ['nullable', 'string', 'max:50'],
            'items'                      => ['required_without:cart_token', 'nullable', 'array'],
            'items.*.product_id'         => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'           => ['required_with:items', 'integer', 'min:1'],
            'expected_total'             => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
