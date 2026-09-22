<?php

namespace App\Core\Traits;

/**
 * Convention kiểm tra quyền cho FormRequest:
 *
 * Class con override hàm permission() thay vì viết tay authorize():
 *
 *     protected function permission(): ?string
 *     {
 *         return 'pages.create';
 *     }
 *
 * authorize() mặc định sẽ kiểm tra Gate/Spatie qua can().
 * Trả null (mặc định) = không yêu cầu permission (backward compatible).
 *
 * Dùng hàm chứ không dùng property vì PHP 8.2 báo fatal khi class khai báo
 * lại property của trait với default value khác.
 *
 * Dùng được cho cả Admin Blade Form (extends FormRequest, fail sẽ redirect)
 * và API Form (extends BaseRequest, fail trả JSON) — xem
 * docs/architecture/09-base-classes.md §3.3.
 */
trait AuthorizesWithPermission
{
    protected function permission(): ?string
    {
        return null;
    }

    public function authorize(): bool
    {
        $permission = $this->permission();

        if ($permission === null) {
            return true;
        }

        return $this->user()?->can($permission) ?? false;
    }
}
