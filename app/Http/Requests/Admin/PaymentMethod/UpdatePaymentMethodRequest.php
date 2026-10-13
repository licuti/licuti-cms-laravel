<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid') ?? $this->route('payment_method');
        $method = \App\Models\PaymentMethod::where('uuid', $uuid)->first();

        return [
            'code'                 => ['required', 'string', 'max:30', Rule::unique('payment_methods', 'code')->ignore($method?->id)],
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:1000'],
            'instructions'         => ['nullable', 'string', 'max:2000'],
            'is_active'            => ['nullable', 'boolean'],
            'display_order'        => ['nullable', 'integer', 'min:0'],
            'min_order_amount'     => ['nullable', 'numeric', 'min:0'],
            'max_order_amount'     => ['nullable', 'numeric', 'gte:min_order_amount'],
            'supported_currencies' => ['nullable', 'array'],
            'config'               => ['nullable'],
        ];
    }
}