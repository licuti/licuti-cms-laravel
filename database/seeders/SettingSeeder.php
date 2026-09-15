<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'site_name',
                'value' => json_encode(['vi' => 'Licuti CMS', 'en' => 'Licuti CMS']),
                'group' => 'general',
                'type' => 'text',
                'label' => 'Tên website',
                'description' => 'Tên hiển thị trên tiêu đề trình duyệt và trang chủ',
                'is_translatable' => true,
            ],
            [
                'key' => 'slogan',
                'value' => json_encode(['vi' => 'Nền tảng thương mại điện tử', 'en' => 'E-commerce platform']),
                'group' => 'general',
                'type' => 'text',
                'label' => 'Khẩu hiệu (Slogan)',
                'description' => 'Khẩu hiệu hiển thị dưới logo',
                'is_translatable' => true,
            ],
            [
                'key' => 'logo_url',
                'value' => '',
                'group' => 'general',
                'type' => 'image',
                'label' => 'Logo website',
                'description' => 'Logo chính của website',
                'is_translatable' => false,
            ],
            [
                'key' => 'favicon_url',
                'value' => '',
                'group' => 'general',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Icon nhỏ trên tab trình duyệt',
                'is_translatable' => false,
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@licuti.com',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Email liên hệ',
                'description' => 'Email dùng để khách hàng liên hệ',
                'is_translatable' => false,
            ],
            [
                'key' => 'contact_phone',
                'value' => '0123456789',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Hotline',
                'description' => 'Số điện thoại hotline',
                'is_translatable' => false,
            ],
            [
                'key' => 'contact_address',
                'value' => json_encode(['vi' => 'Hà Nội, Việt Nam', 'en' => 'Hanoi, Vietnam']),
                'group' => 'general',
                'type' => 'textarea',
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ văn phòng / cửa hàng chính',
                'is_translatable' => true,
            ],

            // SEO
            [
                'key' => 'seo_meta_title',
                'value' => json_encode(['vi' => 'Licuti CMS - Trang chủ', 'en' => 'Licuti CMS - Home']),
                'group' => 'seo',
                'type' => 'text',
                'label' => 'Meta Title mặc định',
                'description' => 'Thẻ title hiển thị trên Google nếu trang không có cấu hình riêng',
                'is_translatable' => true,
            ],
            [
                'key' => 'seo_meta_description',
                'value' => json_encode(['vi' => 'Hệ thống quản trị nội dung Licuti', 'en' => 'Licuti content management system']),
                'group' => 'seo',
                'type' => 'textarea',
                'label' => 'Meta Description mặc định',
                'description' => 'Thẻ mô tả hiển thị trên kết quả tìm kiếm Google',
                'is_translatable' => true,
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'group' => 'seo',
                'type' => 'text',
                'label' => 'Google Analytics ID',
                'description' => 'Mã theo dõi GA (VD: G-XXXXXXXXXX)',
                'is_translatable' => false,
            ],

            // Social
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/',
                'group' => 'social',
                'type' => 'text',
                'label' => 'Facebook URL',
                'description' => 'Link Fanpage Facebook',
                'is_translatable' => false,
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/',
                'group' => 'social',
                'type' => 'text',
                'label' => 'YouTube URL',
                'description' => 'Link kênh YouTube',
                'is_translatable' => false,
            ],

            // Appearance
            [
                'key' => 'primary_color',
                'value' => '#3b82f6',
                'group' => 'appearance',
                'type' => 'text',
                'label' => 'Màu chủ đạo (Primary Color)',
                'description' => 'Màu sắc chính cho nút bấm và điểm nhấn frontend',
                'is_translatable' => false,
            ],
            [
                'key' => 'footer_copyright',
                'value' => json_encode(['vi' => '© 2026 Licuti CMS. All rights reserved.', 'en' => '© 2026 Licuti CMS. All rights reserved.']),
                'group' => 'appearance',
                'type' => 'text',
                'label' => 'Footer Copyright',
                'description' => 'Dòng bản quyền dưới cùng trang web',
                'is_translatable' => true,
            ],
        ];

        DB::table('settings')->truncate();
        
        $now = now();
        foreach ($settings as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }

        DB::table('settings')->insert($settings);
    }
}
