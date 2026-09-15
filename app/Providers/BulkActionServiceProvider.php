<?php

namespace App\Providers;

use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Enums\ContentStatus;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
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
            }
        );

        $registry->register(
            'post_categories',
            'activate',
            __('Kích hoạt'),
            function (array $ids) use ($repo) {
                DB::transaction(fn() => $repo->updateByUuids($ids, ['is_active' => true]));
            }
        );

        $registry->register(
            'post_categories',
            'deactivate',
            __('Vô hiệu hóa'),
            function (array $ids) use ($repo) {
                DB::transaction(fn() => $repo->updateByUuids($ids, ['is_active' => false]));
            }
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
            fn(array $ids) => $repo->deleteByIds($ids)
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'posts',
                'status_' . $status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn(array $ids) => $repo->updateStatusByIds($ids, $status->value)
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
            fn(array $ids) => $repo->deleteByIds($ids)
        );

        foreach (ContentStatus::cases() as $status) {
            $registry->register(
                'pages',
                'status_' . $status->value,
                __('Chuyển trạng thái: :label', ['label' => $status->label()]),
                fn(array $ids) => $repo->updateStatusByIds($ids, $status->value)
            );
        }
    }
}
