<?php

namespace App\Http\Requests\Admin;

use App\Core\BulkAction\BulkActionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $registry = app(BulkActionRegistry::class);
        $module   = $this->input('bulk_module');

        $validActions = ($module && $registry->hasModule($module))
            ? $registry->getActionKeys($module)
            : [];

        return [
            'bulk_module' => ['required', 'string'],
            'action' => [
                'required',
                'string',
                Rule::in($validActions),
            ],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'string'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'action.required' => __('Vui lòng chọn một hành động hàng loạt.'),
            'action.in'       => __('Hành động không hợp lệ cho phân hệ này.'),
            'ids.required'    => __('Vui lòng tích chọn ít nhất một bản ghi để thao tác.'),
            'ids.min'         => __('Vui lòng tích chọn ít nhất một bản ghi để thao tác.'),
        ];
    }
}
