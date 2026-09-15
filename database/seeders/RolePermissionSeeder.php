<?php

namespace Database\Seeders;

use App\Core\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Toàn bộ permissions theo tài liệu thiết kế (docs/04-permissions.md)
     */
    private array $permissions = [
        // Users
        'users.view', 'users.view-detail', 'users.create', 'users.update',
        'users.delete', 'users.restore', 'users.force-delete', 'users.export', 'users.import',

        // Roles
        'roles.view', 'roles.create', 'roles.update', 'roles.delete',

        // Permissions
        'permissions.view', 'permissions.assign', 'permissions.revoke',

        // Categories
        'categories.view', 'categories.create', 'categories.update', 'categories.delete',

        // Brands
        'brands.view', 'brands.create', 'brands.update', 'brands.delete',

        // Products
        'products.view', 'products.view-detail', 'products.create', 'products.update',
        'products.delete', 'products.publish', 'products.approve', 'products.export', 'products.import',

        // Orders
        'orders.view', 'orders.view-all', 'orders.view-detail', 'orders.update',
        'orders.cancel', 'orders.refund', 'orders.export',

        // Inventory
        'inventory.view', 'inventory.import', 'inventory.export', 'inventory.adjust',

        // Coupons
        'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',

        // Posts
        'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',

        // Pages
        'pages.view', 'pages.create', 'pages.update', 'pages.delete',

        // Banners
        'banners.view', 'banners.create', 'banners.update', 'banners.delete',

        // Media
        'media.view', 'media.upload', 'media.delete',

        // Reports
        'reports.sales', 'reports.inventory', 'reports.customers', 'reports.export',

        // Settings
        'settings.view', 'settings.update', 'settings.general',
        'settings.payment', 'settings.email', 'settings.sms',

        // Logs
        'logs.view', 'logs.clear',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo tất cả permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Role: super-admin — Toàn quyền
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Role: admin — Toàn quyền (alias của super-admin để dễ mở rộng)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Role: editor — Quản lý nội dung
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'products.view', 'products.view-detail', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'brands.view',
            'posts.view', 'posts.create', 'posts.update', 'posts.publish',
            'pages.view', 'pages.create', 'pages.update',
            'banners.view', 'banners.create', 'banners.update',
            'media.view', 'media.upload',
        ]);

        // Role: customer — Người dùng thông thường (không có permission admin)
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}
