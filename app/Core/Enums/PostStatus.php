<?php

namespace App\Core\Enums;

enum PostStatus: string
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
