<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const GLOBAL_KEYS = [
        'brand_label',
        'hero_title',
        'hero_description',
        'posts_per_page',
    ];

    public function up(): void
    {
        $now = now();
        $defaults = [
            'brand_label' => [
                'value' => 'Licuti Journal',
                'type' => 'text',
                'label' => 'Nhãn thương hiệu',
                'description' => 'Tên thương hiệu dùng chung cho storefront, không phụ thuộc theme.',
            ],
            'hero_title' => [
                'value' => 'Những ý tưởng đáng để lưu lại.',
                'type' => 'text',
                'label' => 'Tiêu đề hero',
                'description' => 'Tiêu đề nội dung dùng chung trên trang bài viết.',
            ],
            'hero_description' => [
                'value' => 'Góc nhìn, câu chuyện và những điều hữu ích được tuyển chọn cho hành trình kinh doanh số.',
                'type' => 'textarea',
                'label' => 'Mô tả hero',
                'description' => 'Mô tả nội dung dùng chung trên trang bài viết.',
            ],
            'posts_per_page' => [
                'value' => '9',
                'type' => 'number',
                'label' => 'Số bài viết mỗi trang',
                'description' => 'Số bài viết hiển thị mỗi trang trên storefront.',
            ],
        ];

        foreach ($defaults as $key => $definition) {
            $existing = DB::table('settings')->where('key', $key)->first();
            $legacyValue = DB::table('theme_settings')
                ->where('key', $key)
                ->orderByRaw("CASE WHEN theme_slug = 'default' THEN 0 ELSE 1 END")
                ->value('value');

            if (! $existing) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => $legacyValue ?? $definition['value'],
                    'group' => 'appearance',
                    'type' => $definition['type'],
                    'label' => $definition['label'],
                    'description' => $definition['description'],
                    'is_translatable' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Các key này giờ là global; không giữ bản override theo từng theme để tránh 2 nguồn sự thật.
        DB::table('theme_settings')->whereIn('key', self::GLOBAL_KEYS)->delete();
    }

    public function down(): void
    {
        foreach (self::GLOBAL_KEYS as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();

            if ($setting && ! DB::table('theme_settings')->where(['theme_slug' => 'default', 'key' => $key])->exists()) {
                DB::table('theme_settings')->insert([
                    'theme_slug' => 'default',
                    'key' => $key,
                    'value' => $setting->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('settings')->where('key', $key)->delete();
        }
    }
};
