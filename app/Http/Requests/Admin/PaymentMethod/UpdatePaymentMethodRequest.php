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
        $uuid = $this->route('payment_method'); // from route parameters
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('payment_methods', 'code')->ignore($uuid, 'uuid')],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'config' => 'nullable|string',
        ];
    }
}