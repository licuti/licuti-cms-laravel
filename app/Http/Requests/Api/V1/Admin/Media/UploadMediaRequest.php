<?php

namespace App\Http\Requests\Api\V1\Admin\Media;

use App\Core\Base\BaseRequest;

class UploadMediaRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('media.upload');
    }

    public function rules(): array
    {
        return [
            'file'         => ['required_without:files', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip', 'max:10240'], // Max 10MB
            'files'        => ['required_without:file', 'array', 'max:10'], // Tối đa 10 file cùng lúc
            'files.*'      => ['file', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
            'collection'   => ['nullable', 'string', 'max:50'],
            'model_type'   => ['nullable', 'string', 'max:100'],
            'model_id'     => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'     => 'Dung lượng tệp tin không được vượt quá 10MB.',
            'files.*.max'  => 'Mỗi tệp tin trong danh sách tải lên không được vượt quá 10MB.',
            'file.mimes'   => 'Định dạng tệp tin không được hỗ trợ (chỉ chấp nhận ảnh, pdf, office, zip).',
            'files.*.mimes'=> 'Định dạng tệp tin không được hỗ trợ.',
        ];
    }
}
