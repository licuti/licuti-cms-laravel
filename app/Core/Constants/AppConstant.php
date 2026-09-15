<?php

namespace App\Core\Constants;

/**
 * Hằng số chung toàn dự án.
 */
final class AppConstant
{
    // ─── Pagination ────────────────────────────────────────────────────────────
    public const PER_PAGE_DEFAULT = 15;
    public const PER_PAGE_MAX     = 100;

    // ─── Upload ────────────────────────────────────────────────────────────────
    public const UPLOAD_MAX_SIZE_MB     = 10;
    public const UPLOAD_IMAGE_DISK      = 'public';
    public const UPLOAD_IMAGE_PATH      = 'uploads/images';
    public const UPLOAD_ALLOWED_IMAGES  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    public const UPLOAD_ALLOWED_DOCS    = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    // ─── Cache TTL (seconds) ───────────────────────────────────────────────────
    public const CACHE_TTL_SHORT  = 300;        // 5 phút
    public const CACHE_TTL_MEDIUM = 3600;       // 1 giờ
    public const CACHE_TTL_LONG   = 86400;      // 24 giờ
    public const CACHE_TTL_WEEK   = 604800;     // 7 ngày

    // ─── Order ─────────────────────────────────────────────────────────────────
    public const ORDER_NUMBER_PREFIX = 'ORD';

    // ─── Product ───────────────────────────────────────────────────────────────
    public const PRODUCT_LOW_STOCK_THRESHOLD = 5;

    // ─── Gender ────────────────────────────────────────────────────────────────
    public const GENDERS = ['male', 'female', 'other'];
}
