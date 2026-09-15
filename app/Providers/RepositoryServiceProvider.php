<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Bind Interface ↔ Implementation cho toàn bộ Repositories.
 * Mỗi lần thêm Repository mới, đăng ký binding tại đây.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─── User ──────────────────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class,
        );

        // ─── Role & Permission ─────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\RoleRepositoryInterface::class,
            \App\Repositories\RoleRepository::class,
        );

        // ─── Media ─────────────────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\MediaRepositoryInterface::class,
            \App\Repositories\MediaRepository::class,
        );

        $this->app->bind(
            \App\Repositories\Interfaces\MediaFolderRepositoryInterface::class,
            \App\Repositories\MediaFolderRepository::class,
        );

        // ─── Language ──────────────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\LanguageRepositoryInterface::class,
            \App\Repositories\LanguageRepository::class,
        );

        // ─── Settings ──────────────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\SettingRepositoryInterface::class,
            \App\Repositories\SettingRepository::class,
        );

        // ─── Product ───────────────────────────────────────────────────────────
        // $this->app->bind(
        //     \App\Repositories\Interfaces\ProductRepositoryInterface::class,
        //     \App\Repositories\ProductRepository::class,
        // );

        // ─── CMS ───────────────────────────────────────────────────────────────
        $this->app->bind(
            \App\Repositories\Interfaces\PostCategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\PostCategoryRepository::class,
        );
        $this->app->bind(
            \App\Repositories\Interfaces\TagRepositoryInterface::class,
            \App\Repositories\Eloquent\TagRepository::class,
        );
        $this->app->bind(
            \App\Repositories\Interfaces\PostRepositoryInterface::class,
            \App\Repositories\Eloquent\PostRepository::class,
        );
        $this->app->bind(
            \App\Repositories\Interfaces\PageRepositoryInterface::class,
            \App\Repositories\Eloquent\PageRepository::class,
        );
        $this->app->bind(
            \App\Repositories\Interfaces\BannerRepositoryInterface::class,
            \App\Repositories\Eloquent\BannerRepository::class,
        );
        $this->app->bind(\App\Repositories\Interfaces\MenuRepositoryInterface::class, \App\Repositories\Eloquent\MenuRepository::class);

        // ─── CATALOG (PHASE 2) ────────────────────────────────────────────────
        $this->app->bind(\App\Repositories\Interfaces\CategoryRepositoryInterface::class, \App\Repositories\Eloquent\CategoryRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\BrandRepositoryInterface::class, \App\Repositories\Eloquent\BrandRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ProductRepositoryInterface::class, \App\Repositories\Eloquent\ProductRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ProductAttributeRepositoryInterface::class, \App\Repositories\Eloquent\ProductAttributeRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ProductVariantRepositoryInterface::class, \App\Repositories\Eloquent\ProductVariantRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ProductReviewRepositoryInterface::class, \App\Repositories\Eloquent\ProductReviewRepository::class);

        // ─── SALES & PROMOTIONS (PHASE 3) ─────────────────────────────────────
        $this->app->bind(\App\Repositories\Interfaces\CartRepositoryInterface::class, \App\Repositories\Eloquent\CartRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\OrderRepositoryInterface::class, \App\Repositories\Eloquent\OrderRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\PaymentMethodRepositoryInterface::class, \App\Repositories\Eloquent\PaymentMethodRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\PaymentRepositoryInterface::class, \App\Repositories\Eloquent\PaymentRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\CouponRepositoryInterface::class, \App\Repositories\Eloquent\CouponRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\FlashSaleRepositoryInterface::class, \App\Repositories\Eloquent\FlashSaleRepository::class);

        // ─── INVENTORY (PHASE 4) ──────────────────────────────────────────────
        $this->app->bind(\App\Repositories\Interfaces\WarehouseRepositoryInterface::class, \App\Repositories\Eloquent\WarehouseRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\InventoryRepositoryInterface::class, \App\Repositories\Eloquent\InventoryRepository::class);
    }
}
