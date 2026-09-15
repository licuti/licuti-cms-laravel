<?php

namespace App\Http\Requests\Api\V1\Admin\Role;

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
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'guard_name'    => ['nullable', 'string', 'in:web,api'],
        ];
    }
}
