<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SEO_SETTINGS = [
        'seo_og_image' => [
            'value' => '',
            'type' => 'image',
            'label' => 'Ảnh Open Graph mặc định',
            'description' => 'Ảnh đại diện khi chia sẻ trang lên Facebook, Zalo hoặc mạng xã hội khác',
        ],
        'seo_canonical_url' => [
            'value' => '',
            'type' => 'text',
            'label' => 'Canonical URL mặc định',
            'description' => 'Địa chỉ canonical mặc định của website',
        ],
        'google_search_console_verification' => [
            'value' => '',
            'type' => 'text',
            'label' => 'Google Search Console Verification',
            'description' => 'Giá trị content trong thẻ meta xác minh Google Search Console',
        ],
    ];

    private const ANALYTICS_SETTINGS = [
        'google_analytics_id' => [
            'value' => '',
            'label' => 'Google Analytics ID',
            'description' => 'Mã theo dõi GA, ví dụ: G-XXXXXXXXXX',
        ],
        'google_tag_manager_id' => [
            'value' => '',
            'label' => 'Google Tag Manager ID',
            'description' => 'Mã container GTM, ví dụ: GTM-XXXXXXX',
        ],
        'meta_pixel_id' => [
            'value' => '',
            'label' => 'Meta Pixel ID',
            'description' => 'Mã Meta Pixel dùng cho Facebook Ads',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::SEO_SETTINGS as $key => $definition) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => $definition['value'],
                'group' => 'seo',
                'type' => $definition['type'],
                'label' => $definition['label'],
                'description' => $definition['description'],
                'is_translatable' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::ANALYTICS_SETTINGS as $key => $definition) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => DB::table('settings')->where('key', $key)->value('value') ?? $definition['value'],
                    'group' => 'analytics',
                    'type' => 'text',
                    'label' => $definition['label'],
                    'description' => $definition['description'],
                    'is_translatable' => false,
                    'updated_at' => $now,
                ]
            );
        }

        Cache::forget('app_settings');
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys(self::SEO_SETTINGS))->delete();
        DB::table('settings')->whereIn('key', ['google_tag_manager_id', 'meta_pixel_id'])->delete();
        DB::table('settings')->where('key', 'google_analytics_id')->update(['group' => 'seo']);

        Cache::forget('app_settings');
    }
};
