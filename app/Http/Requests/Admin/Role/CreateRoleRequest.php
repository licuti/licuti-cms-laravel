<?php

namespace App\Http\Requests\Admin\Role;

use App\Core\Base\BaseRequest;

class CreateRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles,name',
        ];
    }
}
