<?php

namespace App\Core\BulkAction;

use Closure;
use InvalidArgumentException;

class BulkActionRegistry
{
    /**
     * @var array<string, array<string, array{label: string, handler: Closure}>>
     */
    private array $actions = [];

    /**
     * Đăng ký một action cho một module.
     *
     * @param string  $module  Tên module (vd: 'posts', 'post_categories')
     * @param string  $action  Tên action nội bộ (vd: 'delete', 'activate')
     * @param string  $label   Nhãn hiển thị trong dropdown (vd: 'Xóa đã chọn')
     * @param Closure $handler Logic thực thi, nhận vào mảng IDs
     */
    public function register(string $module, string $action, string $label, Closure $handler): void
    {
        $this->actions[$module][$action] = [
            'label'   => $label,
            'handler' => $handler,
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
     * Lấy danh sách action kèm label để render dropdown trong View.
     *
     * @return array<string, string>  key => label
     */
    public function getActionOptions(string $module): array
    {
        return array_map(
            fn($action) => $action['label'],
            $this->actions[$module] ?? []
        );
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
