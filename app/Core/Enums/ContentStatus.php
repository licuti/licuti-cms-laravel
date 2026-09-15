<?php

namespace App\Core\Enums;

/**
 * Quy chuẩn chung cho trạng thái nội dung (Post, Page, và các module CMS khác).
 *
 * Áp dụng cho mọi entity có vòng đời: Bản nháp → Đã xuất bản → Lưu trữ.
 */
enum ContentStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT     = 'draft';
    case ARCHIVED  = 'archived';

    public function label(): string
    {
        return match($this) {
            self::PUBLISHED => __('Đã xuất bản'),
            self::DRAFT     => __('Bản nháp'),
            self::ARCHIVED  => __('Lưu trữ'),
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PUBLISHED => 'green',
            self::DRAFT     => 'amber',
            self::ARCHIVED  => 'gray',
        };
    }
}
