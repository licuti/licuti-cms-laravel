<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class, // 1. Tạo roles & permissions trước
            AdminUserSeeder::class,      // 2. Tạo admin user và gán role
        ]);
    }
}
