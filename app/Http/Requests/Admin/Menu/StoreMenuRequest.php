<?php

namespace App\Http\Requests\Admin\Menu;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'menus.create';
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'slug'                  => ['nullable', 'string', 'max:255', 'unique:menus,slug'],
            'location'              => ['nullable', 'string', 'max:50'],
            'is_active'             => ['nullable', 'boolean'],
            'items'                 => ['nullable', 'array'],
            'items.*.title'         => ['nullable', 'string', 'max:255'],
            'items.*.url'           => ['nullable', 'string', 'max:255'],
            'items.*.target'        => ['nullable', 'string', 'in:_self,_blank'],
            'items.*.display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Vui lòng nhập tên menu.'),
            'slug.unique'   => __('Mã định danh (slug) này đã được sử dụng.'),
        ];
    }
}
