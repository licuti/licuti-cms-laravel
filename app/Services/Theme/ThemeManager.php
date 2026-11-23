<?php

namespace App\Services\Theme;

use App\Contracts\Theme\ThemeSettingsProvider;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Services\Admin\Setting\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use RuntimeException;

class ThemeManager
{
    private const DEFAULT_THEME = 'default';

    public function __construct(
        private readonly SettingService $settingService,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly ThemeSettingsService $themeSettingsService,
    ) {}

    public function installed(): array
    {
        $themes = [];

        foreach (glob(resource_path('themes/*'), GLOB_ONLYDIR) ?: [] as $directory) {
            $theme = $this->find(basename($directory));
            if ($theme) {
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    public function active(): array
    {
        $previewSlug = session('theme_preview');
        if ($previewSlug && auth()->user()?->can('themes.preview')) {
            $preview = $this->find((string) $previewSlug);
            if ($preview) {
                return $preview;
            }
        }

        $slug = $this->settingService->get('active_theme', self::DEFAULT_THEME);
        $theme = $this->find((string) $slug);

        return $theme ?? $this->find(self::DEFAULT_THEME) ?? throw new RuntimeException('Default storefront theme is not installed.');
    }

    public function activate(string $slug): array
    {
        $theme = $this->find($slug);
        if (! $theme) {
            throw new RuntimeException('Theme không hợp lệ hoặc chưa được cài đặt.');
        }

        $this->settingRepository->updateOrCreateByKey('active_theme', [
            'value' => $slug,
            'group' => 'appearance',
            'type' => 'text',
            'label' => 'Theme đang kích hoạt',
            'description' => 'Theme storefront đang được sử dụng.',
            'is_translatable' => false,
        ]);
        Cache::forget(SettingService::CACHE_KEY);
        session()->forget('theme_preview');

        return $theme;
    }

    public function preview(string $slug): array
    {
        $theme = $this->find($slug);
        if (! $theme) {
            throw new RuntimeException('Theme không hợp lệ hoặc chưa được cài đặt.');
        }

        session()->put('theme_preview', $slug);

        return $theme;
    }

    public function clearPreview(): void
    {
        session()->forget('theme_preview');
    }

    public function settingsProvider(string $slug): ?ThemeSettingsProvider
    {
        $theme = $this->find($slug);
        $providerPath = $theme ? $theme['path'].'/SettingsProvider.php' : null;

        if (! $providerPath || ! is_file($providerPath)) {
            return null;
        }

        $provider = require $providerPath;

        return $provider instanceof ThemeSettingsProvider ? $provider : null;
    }

    public function settings(): array
    {
        return $this->themeSettingsService->values($this->active()['slug']);
    }

    public function view(string $template, array $data = []): \Illuminate\Contracts\View\View
    {
        $theme = $this->active();
        $this->registerViewNamespace($theme);

        return view('theme::'.ltrim($template, '/'), $data + [
            'theme' => $theme,
            'themeSettings' => $this->themeSettingsService->values($theme['slug']),
        ]);
    }

    public function asset(string $path): string
    {
        $theme = $this->active();
        $relativePath = ltrim($path, '/');
        $themeRoot = realpath(public_path('themes/'.$theme['slug'].'/assets'));

        if (! $themeRoot || $relativePath === '' || str_contains($relativePath, '..')) {
            throw new RuntimeException('Theme asset path không hợp lệ.');
        }

        $absolutePath = realpath($themeRoot.DIRECTORY_SEPARATOR.$relativePath);
        if (! $absolutePath || ! str_starts_with($absolutePath, $themeRoot.DIRECTORY_SEPARATOR) || ! is_file($absolutePath)) {
            throw new RuntimeException('Không tìm thấy theme asset: '.$relativePath);
        }

        return asset('themes/'.$theme['slug'].'/assets/'.$relativePath)
            .'?v='.filemtime($absolutePath);
    }

    public function find(string $slug): ?array
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return null;
        }

        $directory = resource_path('themes/'.$slug);
        $metadataPath = $directory.'/theme.json';

        if (! is_dir($directory) || ! is_file($metadataPath)) {
            return null;
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true);

        if (! is_array($metadata) || ($metadata['slug'] ?? null) !== $slug) {
            return null;
        }

        return [
            ...$metadata,
            'slug' => $slug,
            'path' => $directory,
        ];
    }

    private function registerViewNamespace(array $theme): void
    {
        View::replaceNamespace('theme', [$theme['path'].'/views']);
    }
}
