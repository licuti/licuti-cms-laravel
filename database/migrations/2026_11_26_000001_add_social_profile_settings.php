<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SETTINGS = [
        'social_instagram' => [
            'label' => 'Instagram URL',
            'description' => 'Link trang Instagram',
        ],
        'social_tiktok' => [
            'label' => 'TikTok URL',
            'description' => 'Link trang TikTok',
        ],
        'social_zalo' => [
            'label' => 'Zalo URL',
            'description' => 'Link trang hoặc tài khoản Zalo',
        ],
        'social_twitter' => [
            'label' => 'X/Twitter URL',
            'description' => 'Link trang X/Twitter',
        ],
        'social_linkedin' => [
            'label' => 'LinkedIn URL',
            'description' => 'Link trang LinkedIn',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::SETTINGS as $key => $definition) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => '',
                'group' => 'social',
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
