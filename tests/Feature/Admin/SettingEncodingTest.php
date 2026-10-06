<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class SettingEncodingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->admin->assignRole('admin');

        Language::create([
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
        ]);
        Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => false,
            'is_active' => true,
        ]);
    }

    public function test_settings_page_does_not_show_mojibake(): void
    {
        \DB::table('settings')->insert([
            [
                'key' => 'site_name',
                'value' => json_encode(['vi' => 'Licuti CMS', 'en' => 'Licuti CMS']),
                'group' => 'general',
                'type' => 'text',
                'label' => 'Tên website',
                'description' => 'Tên hiển thị trên tiêu đề trình duyệt và trang chủ',
                'is_translatable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_address',
                'value' => json_encode(['vi' => 'Hà Nội, Việt Nam', 'en' => 'Hanoi, Vietnam']),
                'group' => 'general',
                'type' => 'textarea',
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ văn phòng / cửa hàng chính',
                'is_translatable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.settings.edit', 'general'));

        $response->assertStatus(200);
        $response->assertSee('Tên hiển thị trên tiêu đề trình duyệt và trang chủ', false);
        $response->assertSee('Địa chỉ văn phòng / cửa hàng chính', false);
        $response->assertSee('Hà Nội, Việt Nam', false);
        // Typical double-encoded mojibake markers must not appear.
        $this->assertStringNotContainsString('TÃªn', $response->getContent());
        $this->assertStringNotContainsString('Äá»‹a', $response->getContent());
    }

    public function test_setting_update_preserves_vietnamese(): void
    {
        \DB::table('settings')->insert([
            'key' => 'contact_address',
            'value' => json_encode(['vi' => 'Hà Nội, Việt Nam', 'en' => 'Hanoi, Vietnam']),
            'group' => 'general',
            'type' => 'textarea',
            'label' => 'Địa chỉ',
            'description' => 'Địa chỉ văn phòng / cửa hàng chính',
            'is_translatable' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update', 'general'), [
            'contact_address' => ['vi' => 'Đà Nẵng, Việt Nam', 'en' => 'Da Nang, Vietnam'],
        ]);

        $response->assertRedirect(route('admin.settings.edit', 'general'));
        $row = \DB::table('settings')->where('key', 'contact_address')->first();
        $decoded = json_decode($row->value, true);
        $this->assertSame('Đà Nẵng, Việt Nam', $decoded['vi']);
    }
}
