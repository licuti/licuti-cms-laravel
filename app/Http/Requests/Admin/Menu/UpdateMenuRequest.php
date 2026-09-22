<?php

namespace App\Http\Requests\Admin\Menu;

use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'menus.update';
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');
        $menu = Menu::where('uuid', $uuid)->first();
        $menuId = $menu?->id;

        return [
            'name'                  => ['required', 'string', 'max:255'],
            'slug'                  => ['nullable', 'string', 'max:255', Rule::unique('menus', 'slug')->ignore($menuId)],
            'location'              => ['nullable', 'string', 'max:50'],
            'is_active'             => ['nullable', 'boolean'],
            'items'                 => ['nullable', 'array'],
            'items.*.uuid'          => ['nullable', 'string'],
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
