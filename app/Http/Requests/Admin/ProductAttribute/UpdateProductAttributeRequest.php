<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}