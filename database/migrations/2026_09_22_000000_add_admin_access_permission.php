<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Permission chuyên biệt cho quyền truy cập khu vực Quản trị.
     * Được cấp cho super-admin / admin / editor (xem RolePermissionSeeder).
     * Gate: User::canAccessAdmin() = is_admin || can('admin.access').
     */
    public function up(): void
    {
        foreach (['web', 'api'] as $guard) {
            Permission::firstOrCreate([
                'name'       => 'admin.access',
                'guard_name' => $guard,
            ]);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'admin.access')->delete();
    }
};
