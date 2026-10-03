<?php

namespace App\Providers;

use App\Core\BulkAction\BulkActionRegistry;
use App\Core\Export\ExportRegistry;
use App\Core\Filter\FilterRegistry;
use App\Core\Menu\SidebarMenuRegistry;
use App\Core\Search\SearchRegistry;
use App\Core\Table\TableColumnRegistry;
use App\Core\Widget\WidgetRegistry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductAttributeObserver;
use App\Observers\ProductObserver;
use App\Services\Admin\Setting\SettingService;
use App\Services\Shared\Language\LanguageResolver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BulkActionRegistry::class);
        $this->app->singleton(TableColumnRegistry::class);
        $this->app->singleton(FilterRegistry::class);
        $this->app->singleton(SidebarMenuRegistry::class);
        $this->app->singleton(ExportRegistry::class);
        $this->app->singleton(WidgetRegistry::class);
        $this->app->singleton(SearchRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observers — invalidate cache khi model thay đổi.
        Product::observe(ProductObserver::class);
        ProductAttribute::observe(ProductAttributeObserver::class);
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);

        // Cờ is_admin = toàn quyền (super-user bypass).
        // Giữ backward compatibility: các user có is_admin=true (tạo trước khi
        // có Spatie permission) không bị chặn khi permission layer bật dần.
        Gate::before(fn ($user) => $user instanceof User && $user->is_admin);

        try {
            if (Schema::hasTable('settings')) {
                // Settings were previously fetched via Service, let's keep it that way if that's what was used before to get Cached.
                // Or I can use SettingService here
                $settings = app(SettingService::class)->getAllCached();
                View::share('globalSettings', $settings);
            }

            // Share Active Languages to all views
            if (Schema::hasTable('languages')) {
                $languageResolver = app(LanguageResolver::class);
                View::share('activeLanguages', $languageResolver->getActiveLanguages());
            }
        } catch (\Exception $e) {
            // Ignore when db not ready
        }
    }
}
