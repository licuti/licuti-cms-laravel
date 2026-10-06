<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorization cho 2 route move-up / move-down value.
 * Theo convention "write route do FormRequest đảm nhiệm".
 */
class MoveProductAttributeValueRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'product-attributes.update';
    }

    public function rules(): array
    {
        return [];
    }
}
