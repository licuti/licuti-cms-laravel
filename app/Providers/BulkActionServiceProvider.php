<?php

namespace App\Providers;

use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Enums\ContentStatus;
use App\Models\Product;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\Admin\Category\CategoryService;
use App\Services\Admin\Product\ProductService;
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
                DB::transaction(fn() => $repo->updateByUuids($ids, ['is_active' => true]));
            },
            'post-categories.update'
        );

        $registry->register(
            'post_categories',
            'deactivate',
            __('Vô hiệu hóa'),
            function (array $ids) use ($repo) {
                DB::transaction(fn() => $repo->updateByUuids($ids, ['is_active' => false]));
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
            fn(array $ids) => $repo->deleteByIds($ids),
            'posts.delete'
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'posts',
                'status_' . $status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn(array $ids) => $repo->updateStatusByIds($ids, $status->value),
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
            fn(array $ids) => $repo->deleteByIds($ids),
            'pages.delete'
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'pages',
                'status_' . $status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn(array $ids) => $repo->updateStatusByIds($ids, $status->value),
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
            fn(array $ids) => $service->bulkAction('delete', $ids),
            'categories.delete'
        );

        $registry->register(
            'categories',
            'status_1',
            __('Chuyển trạng thái: Hoạt động'),
            fn(array $ids) => $service->bulkAction('status_1', $ids),
            'categories.update'
        );

        $registry->register(
            'categories',
            'status_0',
            __('Chuyển trạng thái: Đã ẩn'),
            fn(array $ids) => $service->bulkAction('status_0', $ids),
            'categories.update'
        );
    }

    // =========================================================================
    // Module: Sản phẩm
    // =========================================================================

    private function bootProductBulkActions(BulkActionRegistry $registry): void
    {
        $service = $this->app->make(ProductService::class);

        $registry->register(
            'products',
            'delete',
            __('Xóa đã chọn'),
            function (array $ids) use ($service) {
                DB::transaction(function () use ($ids, $service) {
                    $products = Product::whereIn('id', $ids)->get();
                    foreach ($products as $product) {
                        $service->delete($product->uuid);
                    }
                });
            },
            'products.delete'
        );

        $statuses = [
            'published' => __('Đã xuất bản'),
            'draft'     => __('Bản nháp'),
            'archived'  => __('Lưu trữ'),
        ];

        foreach ($statuses as $status => $label) {
            $registry->register(
                'products',
                $status,
                __('Chuyển trạng thái: :label', ['label' => $label]),
                fn(array $ids) => Product::whereIn('id', $ids)
                    ->get()
                    ->each(fn($product) => $product->update(['status' => $status])),
                'products.update'
            );
        }
    }
}
