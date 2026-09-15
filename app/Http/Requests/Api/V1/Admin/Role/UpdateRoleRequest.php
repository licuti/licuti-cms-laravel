<?php

namespace App\Http\Requests\Api\V1\Admin\Role;

use App\Core\Base\BaseRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $identifier = $this->route('role');
        $role = is_numeric($identifier) ? Role::find($identifier) : Role::where('name', $identifier)->first();
        $roleId = $role ? $role->id : null;

        return [
            'name'          => ['sometimes', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
