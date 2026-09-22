<?php

namespace App\Providers;

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
        $this->app->singleton(\App\Core\BulkAction\BulkActionRegistry::class);
        $this->app->singleton(\App\Core\Table\TableColumnRegistry::class);
        $this->app->singleton(\App\Core\Filter\FilterRegistry::class);
        $this->app->singleton(\App\Core\Menu\SidebarMenuRegistry::class);
        $this->app->singleton(\App\Core\Export\ExportRegistry::class);
        $this->app->singleton(\App\Core\Widget\WidgetRegistry::class);
        $this->app->singleton(\App\Core\Search\SearchRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cờ is_admin = toàn quyền (super-user bypass).
        // Giữ backward compatibility: các user có is_admin=true (tạo trước khi
        // có Spatie permission) không bị chặn khi permission layer bật dần.
        Gate::before(fn ($user) => $user instanceof \App\Models\User && $user->is_admin);

        try {
            if (Schema::hasTable('settings')) {
                // Settings were previously fetched via Service, let's keep it that way if that's what was used before to get Cached.
                // Or I can use SettingService here
                $settings = app(\App\Services\Admin\Setting\SettingService::class)->getAllCached();
                View::share('globalSettings', $settings);
            }

            // Share Active Languages to all views
            if (Schema::hasTable('languages')) {
                $languageResolver = app(\App\Services\Shared\Language\LanguageResolver::class);
                View::share('activeLanguages', $languageResolver->getActiveLanguages());
            }
        } catch (\Exception $e) {
            // Ignore when db not ready
        }
    }
}
