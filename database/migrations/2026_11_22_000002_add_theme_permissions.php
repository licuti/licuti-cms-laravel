<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['web', 'api'] as $guard) {
            foreach (['themes.view', 'themes.preview', 'themes.activate', 'themes.update'] as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', ['themes.view', 'themes.preview', 'themes.activate', 'themes.update'])->delete();
    }
};
