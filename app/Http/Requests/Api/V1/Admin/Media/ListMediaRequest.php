<?php

namespace App\Http\Requests\Api\V1\Admin\Media;

use App\Core\Base\BaseRequest;

class ListMediaRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.view');
    }

    public function rules(): array
    {
        return [
            'collection' => ['nullable', 'string', 'max:50'],
            'mime_type'  => ['nullable', 'string', 'max:50'],
            'keyword'    => ['nullable', 'string', 'max:100'],
            'model_type' => ['nullable', 'string', 'max:100'],
            'model_id'   => ['nullable', 'integer'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
