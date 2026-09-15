<?php

namespace App\Repositories\Interfaces;

interface LanguageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy danh sách ngôn ngữ đang hoạt động (có cache)
     */
    public function getActiveLanguages();

    /**
     * Xóa cache danh sách ngôn ngữ đang hoạt động
     */
    public function clearCache();

    /**
     * Lấy danh sách ngôn ngữ có phân trang
     */
    public function getPaginatedLanguages(int $perPage = 20);
}
