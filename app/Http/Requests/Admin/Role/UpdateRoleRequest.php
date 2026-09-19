<?php

namespace App\Http\Requests\Admin\Role;

use App\Core\Base\BaseRequest;

class UpdateRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $roleId = $this->route('role');
        
        return [
            'name' => 'required|string|max:100|unique:roles,name,' . $roleId,
        ];
    }
}
