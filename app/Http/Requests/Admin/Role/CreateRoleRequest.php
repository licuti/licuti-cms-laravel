<?php

namespace App\Http\Requests\Admin\Role;

use App\Core\Base\BaseRequest;

class CreateRoleRequest extends BaseRequest
{
    protected function permission(): ?string
    {
        return 'roles.create';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles,name',
        ];
    }
}
