<?php

namespace App\Core\BulkAction;

use Closure;
use InvalidArgumentException;

class BulkActionRegistry
{
    /**
     * @var array<string, array<string, array{label: string, handler: Closure, permission: string|null}>>
     */
    private array $actions = [];

    /**
     * Đăng ký một action cho một module.
     *
     * @param string      $module      Tên module (vd: 'posts', 'post_categories')
     * @param string      $action      Tên action nội bộ (vd: 'delete', 'activate')
     * @param string      $label       Nhãn hiển thị trong dropdown (vd: 'Xóa đã chọn')
     * @param Closure     $handler     Logic thực thi, nhận vào mảng IDs
     * @param string|null $permission  Permission cần có để chạy action (vd: 'pages.delete')
     */
    public function register(string $module, string $action, string $label, Closure $handler, ?string $permission = null): void
    {
        $this->actions[$module][$action] = [
            'label'      => $label,
            'handler'    => $handler,
            'permission' => $permission,
        ];
    }

    /**
     * Lấy danh sách action key của một module (dùng để validate).
     *
     * @return string[]
     */
    public function getActionKeys(string $module): array
    {
        return array_keys($this->actions[$module] ?? []);
    }

    /**
     * Lấy permission gắn với một action (dùng để enforce trong FormRequest).
     */
    public function getPermission(string $module, string $action): ?string
    {
        return $this->actions[$module][$action]['permission'] ?? null;
    }

    /**
     * Lấy danh sách action kèm label để render dropdown trong View.
     *
     * Chỉ trả về action mà user hiện tại có quyền thực thi — dropdown tự ẩn
     * những action user không được phép (single source of truth cùng authorize()).
     *
     * @return array<string, string>  key => label
     */
    public function getActionOptions(string $module): array
    {
        $options = [];

        foreach ($this->actions[$module] ?? [] as $action => $definition) {
            $permission = $definition['permission'];

            if ($permission !== null && auth()->user()?->can($permission) === false) {
                continue;
            }

            $options[$action] = $definition['label'];
        }

        return $options;
    }

    /**
     * Kiểm tra module có được đăng ký không.
     */
    public function hasModule(string $module): bool
    {
        return isset($this->actions[$module]);
    }

    /**
     * Kiểm tra một action có hợp lệ không.
     */
    public function isValid(string $module, string $action): bool
    {
        return isset($this->actions[$module][$action]);
    }

    /**
     * Thực thi action — dispatch tới handler đã đăng ký.
     *
     * @throws InvalidArgumentException nếu action không tồn tại
     */
    public function dispatch(string $module, string $action, array $ids): void
    {
        $handler = $this->actions[$module][$action]['handler'] ?? null;

        if (is_null($handler)) {
            throw new InvalidArgumentException(
                "Bulk action [{$action}] không được đăng ký cho module [{$module}]."
            );
        }

        $handler($ids);
    }
}
