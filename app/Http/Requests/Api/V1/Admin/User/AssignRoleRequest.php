<?php

namespace App\Http\Requests\Api\V1\Admin\User;

use App\Core\Base\BaseRequest;

class AssignRoleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update') || $this->user()->can('users.update');
    }

    public function rules(): array
    {
        return [
            'roles'   => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
