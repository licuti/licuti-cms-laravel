<?php

namespace App\Core\Table;

use Closure;

class TableColumnRegistry
{
    /**
     * @var array<string, array<string, array>>
     */
    private array $columns = [];

    /**
     * Đăng ký một cột cho bảng danh sách.
     *
     * @param string  $module  Tên module (vd: 'posts')
     * @param string  $key     Khóa định danh cột (vd: 'title', 'status')
     * @param string  $label   Tiêu đề cột hiển thị trên Thead
     * @param Closure $render  Hàm render HTML cho từng ô, nhận vào model instance
     * @param array   $options Tùy chọn thêm (class, width, sortable...)
     */
    public function register(string $module, string $key, string $label, Closure $render, array $options = []): void
    {
        $this->columns[$module][$key] = array_merge([
            'label'    => $label,
            'render'   => $render,
            'class'    => '',
            'width'    => null,
            'sortable' => false,
        ], $options);
    }

    /**
     * Lấy danh sách các cột của một module.
     *
     * @return array<string, array>
     */
    public function getColumns(string $module): array
    {
        return $this->columns[$module] ?? [];
    }
}
