<?php

namespace App\Providers;

use App\Repositories\Eloquent\BannerRepository;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CouponRepository;
use App\Repositories\Eloquent\FlashSaleRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\LanguageRepository;
use App\Repositories\Eloquent\MediaFolderRepository;
use App\Repositories\Eloquent\MediaRepository;
use App\Repositories\Eloquent\MenuRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PageRepository;
use App\Repositories\Eloquent\PaymentMethodRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\PostCategoryRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\ProductAttributeRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductReviewRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Eloquent\TagRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WarehouseRepository;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\MediaFolderRepositoryInterface;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductReviewRepositoryInterface;
use App\Repositories\Interfaces\ProductVariantRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
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
            UserRepositoryInterface::class,
            UserRepository::class,
        );

        // ─── Role & Permission ─────────────────────────────────────────────────
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class,
        );

        // ─── Media ─────────────────────────────────────────────────────────────
        $this->app->bind(
            MediaRepositoryInterface::class,
            MediaRepository::class,
        );

        $this->app->bind(
            MediaFolderRepositoryInterface::class,
            MediaFolderRepository::class,
        );

        // ─── Language ──────────────────────────────────────────────────────────
        $this->app->bind(
            LanguageRepositoryInterface::class,
            LanguageRepository::class,
        );

        // ─── Settings ──────────────────────────────────────────────────────────
        $this->app->bind(
            SettingRepositoryInterface::class,
            SettingRepository::class,
        );

        // ─── CMS ───────────────────────────────────────────────────────────────
        $this->app->bind(
            PostCategoryRepositoryInterface::class,
            PostCategoryRepository::class,
        );
        $this->app->bind(
            TagRepositoryInterface::class,
            TagRepository::class,
        );
        $this->app->bind(
            PostRepositoryInterface::class,
            PostRepository::class,
        );
        $this->app->bind(
            PageRepositoryInterface::class,
            PageRepository::class,
        );
        $this->app->bind(
            BannerRepositoryInterface::class,
            BannerRepository::class,
        );
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);

        // ─── CATALOG (PHASE 2) ────────────────────────────────────────────────
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(ProductAttributeRepositoryInterface::class, ProductAttributeRepository::class);
        $this->app->bind(ProductReviewRepositoryInterface::class, ProductReviewRepository::class);

        // ─── SALES & PROMOTIONS (PHASE 3) ─────────────────────────────────────
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(FlashSaleRepositoryInterface::class, FlashSaleRepository::class);

        // ─── INVENTORY (PHASE 4) ──────────────────────────────────────────────
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
    }
}
