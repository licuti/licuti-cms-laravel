<?php

namespace Database\Seeders;

use App\Core\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@licuti.com'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('Admin@12345'),
                'status'   => UserStatus::ACTIVE,
                'is_admin' => true,
                'phone'    => null,
            ]
        );

        $admin->assignRole('admin');

        $this->command->info("✅ Admin created: admin@licuti.com / Admin@12345");
    }
}
