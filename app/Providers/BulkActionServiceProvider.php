<?php

namespace App\Providers;

use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Enums\ContentStatus;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Admin\Category\CategoryService;
use App\Services\Admin\Product\ProductService;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use App\Services\Admin\ProductAttribute\ProductAttributeValueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class BulkActionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $registry = $this->app->make(BulkActionRegistry::class);

        $this->bootPostCategoryBulkActions($registry);
        $this->bootPostBulkActions($registry);
        $this->bootPageBulkActions($registry);
        $this->bootCategoryBulkActions($registry);
        $this->bootProductBulkActions($registry);
        $this->bootProductAttributeBulkActions($registry);
        $this->bootProductAttributeValueBulkActions($registry);
    }

    // =========================================================================
    // Module: Danh mục bài viết
    // =========================================================================

    private function bootPostCategoryBulkActions(BulkActionRegistry $registry): void
    {
        $repo = $this->app->make(PostCategoryRepositoryInterface::class);

        $registry->register(
            'post_categories',
            'delete',
            __('Xóa đã chọn'),
            function (array $ids) use ($repo) {
                DB::transaction(function () use ($ids, $repo) {
                    // Gom 1 query để lấy tất cả, tránh N+1
                    $cats = $repo->findManyByUuids($ids);
                    foreach ($cats as $cat) {
                        $cat->children()->update(['parent_id' => $cat->parent_id]);
                        $repo->delete($cat->id);
                    }
                });
            },
            'post-categories.delete'
        );

        $registry->register(
            'post_categories',
            'activate',
            __('Kích hoạt'),
            function (array $ids) use ($repo) {
                DB::transaction(fn () => $repo->updateByUuids($ids, ['is_active' => true]));
            },
            'post-categories.update'
        );

        $registry->register(
            'post_categories',
            'deactivate',
            __('Vô hiệu hóa'),
            function (array $ids) use ($repo) {
                DB::transaction(fn () => $repo->updateByUuids($ids, ['is_active' => false]));
            },
            'post-categories.update'
        );
    }

    // =========================================================================
    // Module: Bài viết
    // =========================================================================

    private function bootPostBulkActions(BulkActionRegistry $registry): void
    {
        $repo = $this->app->make(PostRepositoryInterface::class);

        $registry->register(
            'posts',
            'delete',
            __('Xóa đã chọn'),
            fn (array $ids) => $repo->deleteByIds($ids),
            'posts.delete'
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'posts',
                'status_'.$status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn (array $ids) => $repo->updateStatusByIds($ids, $status->value),
                'posts.update'
            );
        }
    }

    // =========================================================================
    // Module: Trang tĩnh
    // =========================================================================

    private function bootPageBulkActions(BulkActionRegistry $registry): void
    {
        $repo = $this->app->make(PageRepositoryInterface::class);

        $registry->register(
            'pages',
            'delete',
            __('Xóa đã chọn'),
            fn (array $ids) => $repo->deleteByIds($ids),
            'pages.delete'
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'pages',
                'status_'.$status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn (array $ids) => $repo->updateStatusByIds($ids, $status->value),
                'pages.update'
            );
        }
    }

    // =========================================================================
    // Module: Danh mục sản phẩm
    // =========================================================================

    private function bootCategoryBulkActions(BulkActionRegistry $registry): void
    {
        $service = $this->app->make(CategoryService::class);

        // Giữ nguyên logic service hiện tại (reparent children + null category_id sản phẩm)
        $registry->register(
            'categories',
            'delete',
            __('Xóa đã chọn'),
            fn (array $ids) => $service->bulkAction('delete', $ids),
            'categories.delete'
        );

        $registry->register(
            'categories',
            'status_1',
            __('Chuyển trạng thái: Hoạt động'),
            fn (array $ids) => $service->bulkAction('status_1', $ids),
            'categories.update'
        );

        $registry->register(
            'categories',
            'status_0',
            __('Chuyển trạng thái: Đã ẩn'),
            fn (array $ids) => $service->bulkAction('status_0', $ids),
            'categories.update'
        );
    }

    // =========================================================================
    // Module: Sản phẩm
    // =========================================================================

    private function bootProductBulkActions(BulkActionRegistry $registry): void
    {
        $service = $this->app->make(ProductService::class);
        $repo = $this->app->make(ProductRepositoryInterface::class);

        $registry->register(
            'products',
            'delete',
            __('Xóa đã chọn'),
            function (array $ids) use ($repo) {
                DB::transaction(function () use ($ids, $repo) {
                    // Observer không fire với bulk query → clear cache tay.
                    $products = $repo->findMany($ids, ['id', 'uuid']);
                    $repo->deleteByIds($ids);
                    $products->each(fn ($product) => $repo->clearCache($product));
                });
            },
            'products.delete'
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'products',
                $status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn (array $ids) => $service->updateStatusByIds($ids, $status->value),
                'products.update'
            );
        }
    }

    // =========================================================================
    // Module: Thuộc tính sản phẩm
    // =========================================================================

    private function bootProductAttributeBulkActions(BulkActionRegistry $registry): void
    {
        $service = $this->app->make(ProductAttributeService::class);
        $repo = $this->app->make(ProductAttributeRepositoryInterface::class);

        $registry->register(
            'product_attributes',
            'delete',
            __('Xóa đã chọn'),
            function (array $ids) use ($service, $repo) {
                DB::transaction(function () use ($ids, $service, $repo) {
                    $attributes = $repo->findManyByUuids($ids);
                    foreach ($attributes as $attr) {
                        $service->delete($attr->uuid);
                    }
                });
            },
            'product-attributes.delete'
        );

        $registry->register(
            'product_attributes',
            'filterable_1',
            __('Bật bộ lọc tìm kiếm'),
            function (array $ids) use ($repo) {
                DB::transaction(function () use ($ids, $repo) {
                    $repo->updateByUuids($ids, ['is_filterable' => true]);
                    $repo->clearCache();
                });
            },
            'product-attributes.update'
        );

        $registry->register(
            'product_attributes',
            'filterable_0',
            __('Tắt bộ lọc tìm kiếm'),
            function (array $ids) use ($repo) {
                DB::transaction(function () use ($ids, $repo) {
                    $repo->updateByUuids($ids, ['is_filterable' => false]);
                    $repo->clearCache();
                });
            },
            'product-attributes.update'
        );
    }

    // =========================================================================
    // Module: Giá trị thuộc tính sản phẩm
    // =========================================================================

    private function bootProductAttributeValueBulkActions(BulkActionRegistry $registry): void
    {
        $valueService = $this->app->make(ProductAttributeValueService::class);

        $registry->register(
            'product_attribute_values',
            'delete',
            __('Xóa đã chọn'),
            function (array $ids) use ($valueService) {
                DB::transaction(function () use ($ids, $valueService) {
                    // Loop qua service để giữ nguyên check "đang dùng trong sản phẩm"
                    // và fires model event (observer clear cache).
                    foreach ($ids as $uuid) {
                        $valueService->delete($uuid);
                    }
                });
            },
            'product-attributes.delete'
        );
    }
}
