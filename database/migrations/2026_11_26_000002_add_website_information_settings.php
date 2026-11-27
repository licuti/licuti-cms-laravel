<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SETTINGS = [
        'site_url' => [
            'label' => 'Website URL',
            'description' => 'Địa chỉ website chính thức, ví dụ: https://example.com',
        ],
        'working_hours' => [
            'label' => 'Giờ làm việc',
            'description' => 'Thời gian làm việc hiển thị cho khách truy cập',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::SETTINGS as $key => $definition) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => '',
                'group' => 'general',
                'type' => 'text',
                'label' => $definition['label'],
                'description' => $definition['description'],
                'is_translatable' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Cache::forget('app_settings');
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys(self::SETTINGS))->delete();

        Cache::forget('app_settings');
    }
};
