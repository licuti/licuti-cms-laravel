<?php

namespace App\Core\Constants;

/**
 * Hằng số Cache Key dùng chung toàn dự án.
 * 
 * Quy tắc đặt tên: {module}:{resource}[:{id}]
 * Ví dụ:
 *   - categories:tree
 *   - settings:all
 *   - products:featured
 */
final class CacheKey
{
    // ─── Categories ────────────────────────────────────────────────────────────
    public const CATEGORY_TREE = 'categories:tree';
    public const CATEGORY_ALL  = 'categories:all';

    // ─── Products ──────────────────────────────────────────────────────────────
    public const PRODUCTS_FEATURED = 'products:featured';
    public const PRODUCTS_NEW      = 'products:new';

    // ─── Settings ──────────────────────────────────────────────────────────────
    public const SETTINGS_ALL     = 'settings:all';
    public const SETTINGS_GENERAL = 'settings:general';
    public const SETTINGS_PAYMENT = 'settings:payment';
    public const SETTINGS_EMAIL   = 'settings:email';

    // ─── CMS ───────────────────────────────────────────────────────────────────
    public const BANNERS_HOME    = 'banners:home';
    public const MENUS_HEADER    = 'menus:header';
    public const MENUS_FOOTER    = 'menus:footer';

    // ─── Auth ──────────────────────────────────────────────────────────────────
    public const PERMISSIONS_ALL = 'permissions:all';

    /**
     * Tạo cache key động cho một resource cụ thể theo ID.
     * Ví dụ: CacheKey::forModel('product', 5) → "product:5"
     */
    public static function forModel(string $model, int|string $id): string
    {
        return "{$model}:{$id}";
    }
}
