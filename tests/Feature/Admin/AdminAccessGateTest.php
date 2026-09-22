<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessGateTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoleWithAccess(string $name): User
    {
        $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $role->givePermissionTo('admin.access');

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_user_with_admin_access_permission_can_enter_admin(): void
    {
        $user = $this->makeRoleWithAccess('editor');

        $this->assertTrue($user->canAccessAdmin());
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_user_without_admin_access_permission_is_blocked(): void
    {
        $role = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        // customer có permission admin khác nhưng KHÔNG có admin.access -> vẫn bị chặn
        $role->givePermissionTo(Permission::firstOrCreate(['name' => 'banners.create', 'guard_name' => 'web']));

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($role);

        $this->assertFalse($user->canAccessAdmin());
        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_is_admin_flag_grants_access_without_role(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($user->canAccessAdmin());
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }
}
