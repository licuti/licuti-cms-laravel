<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeSettingsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ThemeAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_theme_assets_are_self_hosted_and_versioned(): void
    {
        $manager = app(ThemeManager::class);

        $cssUrl = $manager->asset('css/theme.css');
        $jsUrl = $manager->asset('js/theme.js');

        $this->assertStringStartsWith('/themes/default/assets/css/theme.css?v=', parse_url($cssUrl, PHP_URL_PATH).'?'.parse_url($cssUrl, PHP_URL_QUERY));
        $this->assertStringStartsWith('/themes/default/assets/js/theme.js?v=', parse_url($jsUrl, PHP_URL_PATH).'?'.parse_url($jsUrl, PHP_URL_QUERY));
        $this->assertStringNotContainsString('resources/', $cssUrl);
        $this->assertStringNotContainsString('resources/', $jsUrl);
    }

    public function test_theme_asset_path_traversal_is_rejected(): void
    {
        $this->expectException(\RuntimeException::class);

        app(ThemeManager::class)->asset('../.env');
    }


    public function test_admin_can_view_installed_themes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.themes.index'))
            ->assertOk()
            ->assertSee('Default Editorial')
            ->assertSee('Tùy biến');
    }

    public function test_theme_customize_form_reads_common_theme_schema_only(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.themes.customize', 'default'))
            ->assertOk()
            ->assertSee('Màu sắc')
            ->assertSee('Màu nhấn')
            ->assertDontSee('Nhận diện thương hiệu')
            ->assertDontSee('Tiêu đề hero');
    }

    public function test_default_theme_options_have_explicit_scopes(): void
    {
        $schema = app(ThemeSettingsService::class)->schema('default');
        $scopes = [];

        foreach ($schema['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $scopes[$field['key']] = $field['scope'] ?? $section['scope'] ?? null;
            }
        }

        $this->assertSame('global', $scopes['brand_label']);
        $this->assertSame('global', $scopes['hero_title']);
        $this->assertSame('global', $scopes['hero_description']);
        $this->assertSame('global', $scopes['posts_per_page']);
        $this->assertSame('common_theme', $scopes['accent_color']);
        $this->assertSame('common_theme', $scopes['header_bg']);
        $this->assertSame('theme_specific', $scopes['header_sticky']);
        $this->assertSame('common_theme', $scopes['blog_layout']);
        $this->assertSame('theme_specific', $scopes['sidebar_layout']);
        $this->assertSame('theme_specific', $scopes['custom_css']);
        $this->assertSame([
            'accent_color',
            'header_bg',
            'footer_bg',
            'header_sticky',
            'show_announcement',
            'announcement_text',
            'blog_layout',
            'grid_columns',
            'show_reading_time',
            'sidebar_layout',
            'show_author_box',
            'show_social_share',
            'related_posts_count',
            'custom_css',
        ], array_keys(app(ThemeSettingsService::class)->fields('default')));
    }

    public function test_global_theme_values_are_read_from_global_settings(): void
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => 'brand_label'],
            [
                'value' => 'Licuti Global',
                'group' => 'appearance',
                'type' => 'text',
            ],
        );

        Cache::forget(\App\Services\Admin\Setting\SettingService::CACHE_KEY);
        app(ThemeSettingsService::class)->forgetCache('default');

        $this->assertSame('Licuti Global', setting('brand_label'));
        $this->assertSame('Licuti Global', app(ThemeSettingsService::class)->values('default')['brand_label']);
    }

    public function test_theme_settings_are_saved_per_theme(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.themes.update', 'default'), [
                'settings' => [
                    'accent_color' => '#112233',
                ],
            ])
            ->assertRedirect(route('admin.themes.customize', 'default'));

        $this->assertDatabaseHas('theme_settings', [
            'theme_slug' => 'default',
            'key' => 'accent_color',
            'value' => '#112233',
        ]);
        $this->assertDatabaseMissing('theme_settings', [
            'theme_slug' => 'default',
            'key' => 'brand_label',
        ]);
        $this->assertSame('Licuti Journal', setting('brand_label'));
    }

    public function test_theme_settings_reject_invalid_color(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.themes.update', 'default'), [
                'settings' => [
                    'accent_color' => 'not-a-color',
                ],
            ])
            ->assertSessionHasErrors('settings.accent_color');
    }

    public function test_custom_css_rejects_style_and_script_tags(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.themes.update', 'default'), [
                'settings' => [
                    'custom_css' => 'body { color: red; }</style><script>alert(1)</script>',
                ],
            ])
            ->assertSessionHasErrors('settings.custom_css');
    }

    public function test_invalid_scope_exception_includes_scope_name(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Theme setting scope không hợp lệ: invalid_scope');

        (new \App\Services\Theme\ThemeSettingsRegistry)->section('test', 'Test', null, 'invalid_scope');
    }

    public function test_preview_does_not_change_active_theme_setting(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.themes.preview', 'default'))
            ->assertRedirect(route('storefront.home'));

        $this->assertSame('default', setting('active_theme', 'default'));
        $this->assertSame('default', session('theme_preview'));
    }

    public function test_activate_theme_persists_active_theme(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.themes.activate', 'default'))
            ->assertRedirect(route('admin.themes.index'));

        $this->assertDatabaseHas('settings', [
            'key' => 'active_theme',
            'value' => 'default',
        ]);
    }

    public function test_user_without_theme_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.themes.index'))
            ->assertForbidden();
    }
}
