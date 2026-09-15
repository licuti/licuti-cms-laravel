<?php

namespace App\Providers;

use App\Core\BulkAction\BulkActionRegistry;
use App\Repositories\Interfaces\PostCategoryRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\Admin\Post\PostService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class BulkActionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $registry = $this->app->make(BulkActionRegistry::class);

        $this->bootPostCategoryBulkActions($registry);
        $this->bootPostBulkActions($registry);
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

        foreach (PostService::STATUSES as $status => $label) {
            $registry->register(
                'posts',
                'status_' . $status,
                __('Chuyển trạng thái: :label', ['label' => $label]),
                fn(array $ids) => $repo->updateStatusByIds($ids, $status)
            );
        }
    }
}
