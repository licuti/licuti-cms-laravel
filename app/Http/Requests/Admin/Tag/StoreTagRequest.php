<?php

namespace App\Http\Requests\Admin\Tag;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'tags.create';
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'submit_action' => ['nullable', 'string', 'in:save,save_and_edit'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'          => __('Tên thẻ tag'),
            'slug'          => __('Đường dẫn (slug)'),
            'display_order' => __('Thứ tự hiển thị'),
        ];
    }
}
