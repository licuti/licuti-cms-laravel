<?php

namespace App\Services\Theme;

use App\Contracts\Theme\ThemeSettingsProvider;
use App\Services\Admin\Setting\SettingService;
use Illuminate\Support\Facades\View;
use RuntimeException;

class ThemeManager
{
    private const DEFAULT_THEME = 'default';

    /** @var array<string, ?array> Memoize metadata theo slug trong vòng đời request */
    private array $themeCache = [];

    public function __construct(
        private readonly SettingService $settingService,
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

    public function activeSlug(): string
    {
        $configured = $this->find((string) $this->settingService->get('active_theme', self::DEFAULT_THEME));

        return $configured['slug'] ?? $this->active()['slug'];
    }

    public function activate(string $slug): array
    {
        $theme = $this->find($slug);
        if (! $theme) {
            throw new RuntimeException('Theme không hợp lệ hoặc chưa được cài đặt.');
        }

        $this->settingService->set('active_theme', $slug, [
            'group' => 'appearance',
            'type' => 'text',
            'label' => 'Theme đang kích hoạt',
            'description' => 'Theme storefront đang được sử dụng.',
            'is_translatable' => false,
        ]);
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
        if (array_key_exists($slug, $this->themeCache)) {
            return $this->themeCache[$slug];
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return $this->themeCache[$slug] = null;
        }

        $directory = resource_path('themes/'.$slug);
        $metadataPath = $directory.'/theme.json';

        if (! is_dir($directory) || ! is_file($metadataPath)) {
            return $this->themeCache[$slug] = null;
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true);

        if (! is_array($metadata) || ($metadata['slug'] ?? null) !== $slug) {
            return $this->themeCache[$slug] = null;
        }

        return $this->themeCache[$slug] = [
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
