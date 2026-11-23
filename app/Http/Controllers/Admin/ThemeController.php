<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Http\Requests\Admin\Theme\UpdateThemeSettingsRequest;
use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeSettingsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThemeController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ThemeManager $themeManager,
        private readonly ThemeSettingsService $themeSettingsService,
    ) {}

    public function index(): View
    {
        $this->authorize('themes.view');

        $active = $this->themeManager->active();
        $configuredTheme = $this->themeManager->find((string) setting('active_theme', 'default'));
        $activeSlug = $configuredTheme['slug'] ?? $active['slug'];

        return view('admin.themes.index', [
            'themes' => $this->themeManager->installed(),
            'activeSlug' => $activeSlug,
            'previewSlug' => session('theme_preview'),
        ]);
    }

    public function customize(string $slug): View
    {
        $this->authorize('themes.view');
        $theme = $this->themeOr404($slug);

        return view('admin.themes.customize', [
            'theme' => $theme,
            'schema' => $this->themeSettingsService->themeSchema($slug),
            'values' => $this->themeSettingsService->values($slug),
        ]);
    }

    public function update(UpdateThemeSettingsRequest $request, string $slug): RedirectResponse
    {
        $this->themeOr404($slug);
        $this->themeSettingsService->save($slug, $request->validated('settings', []));

        return redirect()->route('admin.themes.customize', $slug)
            ->with('success', 'Đã lưu cấu hình theme.');
    }

    public function preview(string $slug): RedirectResponse
    {
        $this->authorize('themes.preview');
        $this->themeManager->preview($slug);

        return redirect()->route('storefront.home')
            ->with('success', 'Đang xem trước theme. Chỉ phiên làm việc của bạn bị ảnh hưởng.');
    }

    public function clearPreview(): RedirectResponse
    {
        $this->authorize('themes.preview');
        $this->themeManager->clearPreview();

        return back()->with('success', 'Đã tắt chế độ xem trước theme.');
    }

    public function activate(string $slug): RedirectResponse
    {
        $this->authorize('themes.activate');
        $this->themeManager->activate($slug);

        return redirect()->route('admin.themes.index')
            ->with('success', 'Đã kích hoạt theme.');
    }

    private function themeOr404(string $slug): array
    {
        $theme = $this->themeManager->find($slug);

        if (! $theme) {
            abort(404);
        }

        return $theme;
    }
}
