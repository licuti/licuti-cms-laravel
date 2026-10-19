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
            'payment_method_code'     => ['required', 'string', 'exists:payment_methods,code'],
            'recipient_name'          => ['required', 'string', 'max:100'],
            'phone'                   => ['required', 'string', 'max:20'],
            'email'                   => ['nullable', 'email', 'max:100'],
            'address_line'            => ['required', 'string', 'max:255'],
            'ward'                    => ['nullable', 'string', 'max:100'],
            'district'                => ['nullable', 'string', 'max:100'],
            'province'                => ['nullable', 'string', 'max:100'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
        ];
    }
}
