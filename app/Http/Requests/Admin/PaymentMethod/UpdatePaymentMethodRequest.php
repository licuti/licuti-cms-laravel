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
            'code' => ['required', 'string', 'max:30', Rule::unique('payment_methods', 'code')->ignore($method?->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'config' => 'nullable|string',
        ];
    }
}