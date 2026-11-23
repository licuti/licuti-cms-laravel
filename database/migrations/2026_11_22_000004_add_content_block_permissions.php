<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['web', 'api'] as $guard) {
            foreach (['content-blocks.view', 'content-blocks.create', 'content-blocks.update', 'content-blocks.delete', 'content-blocks.publish'] as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'content-blocks.view',
            'content-blocks.create',
            'content-blocks.update',
            'content-blocks.delete',
            'content-blocks.publish',
        ])->delete();
    }
};
