<?php

namespace App\Http\Requests\Admin\Setting;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'settings.update';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Các settings gửi lên thường dạng mảng dynamic key-value
            // Ta có thể không validate chặt chẽ từng key vì nó tùy thuộc vào group
            // Có thể thêm custom rules nếu cần thiết sau này
        ];
    }
}
