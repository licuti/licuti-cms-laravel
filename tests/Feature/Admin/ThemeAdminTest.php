<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Theme\ThemeSettingsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_can_view_installed_themes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.themes.index'))
            ->assertOk()
            ->assertSee('Default Editorial')
            ->assertSee('Tùy biến');
    }

    public function test_theme_customize_form_reads_schema_defaults(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.themes.customize', 'default'))
            ->assertOk()
            ->assertSee('Nhận diện thương hiệu')
            ->assertSee('Licuti Journal')
            ->assertSee('Những ý tưởng đáng để lưu lại.');
    }

    public function test_theme_settings_are_saved_per_theme(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.themes.update', 'default'), [
                'settings' => [
                    'brand_label' => 'Licuti Stories',
                    'accent_color' => '#112233',
                    'hero_title' => 'Câu chuyện mới',
                    'hero_description' => 'Mô tả mới',
                    'posts_per_page' => 12,
                ],
            ])
            ->assertRedirect(route('admin.themes.customize', 'default'));

        $this->assertDatabaseHas('theme_settings', [
            'theme_slug' => 'default',
            'key' => 'brand_label',
            'value' => 'Licuti Stories',
        ]);
        $this->assertSame('Licuti Stories', app(ThemeSettingsService::class)->values('default')['brand_label']);
    }

    public function test_theme_settings_reject_invalid_color(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.themes.update', 'default'), [
                'settings' => [
                    'brand_label' => 'Licuti Stories',
                    'accent_color' => 'not-a-color',
                    'hero_title' => 'Câu chuyện mới',
                    'hero_description' => 'Mô tả mới',
                    'posts_per_page' => 12,
                ],
            ])
            ->assertSessionHasErrors('settings.accent_color');
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
