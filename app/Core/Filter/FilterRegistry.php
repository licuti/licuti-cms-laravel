<?php

namespace App\Core\Filter;

class FilterRegistry
{
    private array $filters = [];

    /**
     * Đăng ký một bộ lọc.
     */
    public function register(string $module, string $filterKey, string $type, array $config = []): void
    {
        $this->filters[$module][$filterKey] = array_merge(['type' => $type], $config);
    }

    /**
     * Lấy danh sách bộ lọc của một module.
     */
    public function getFilters(string $module): array
    {
        return $this->filters[$module] ?? [];
    }
}
