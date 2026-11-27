<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SETTINGS = [
        'mail_enabled' => [
            'value' => '1',
            'group' => 'mail',
            'type' => 'boolean',
            'label' => 'Bật gửi email',
            'description' => 'Cho phép hệ thống gửi email thông báo',
        ],
        'mail_test_recipient' => [
            'value' => '',
            'group' => 'mail',
            'type' => 'text',
            'label' => 'Email nhận thử nghiệm',
            'description' => 'Địa chỉ nhận email khi kiểm tra cấu hình SMTP',
        ],
        'security_two_factor_enabled' => [
            'value' => '0',
            'group' => 'security',
            'type' => 'boolean',
            'label' => 'Bật xác thực hai bước',
            'description' => 'Bật tùy chọn xác thực hai bước cho tài khoản quản trị',
        ],
        'security_captcha_enabled' => [
            'value' => '0',
            'group' => 'security',
            'type' => 'boolean',
            'label' => 'Bật CAPTCHA',
            'description' => 'Bảo vệ các biểu mẫu đăng nhập và gửi dữ liệu bằng CAPTCHA',
        ],
        'security_recaptcha_site_key' => [
            'value' => '',
            'group' => 'security',
            'type' => 'text',
            'label' => 'reCAPTCHA Site Key',
            'description' => 'Site Key của Google reCAPTCHA',
        ],
        'security_recaptcha_secret_key' => [
            'value' => '',
            'group' => 'security',
            'type' => 'text',
            'label' => 'reCAPTCHA Secret Key',
            'description' => 'Secret Key của Google reCAPTCHA',
        ],
        'security_session_lifetime' => [
            'value' => '120',
            'group' => 'security',
            'type' => 'number',
            'label' => 'Thời gian phiên đăng nhập (phút)',
            'description' => 'Thời gian hết hạn phiên đăng nhập quản trị',
        ],
        'security_max_login_attempts' => [
            'value' => '5',
            'group' => 'security',
            'type' => 'number',
            'label' => 'Số lần đăng nhập sai tối đa',
            'description' => 'Số lần đăng nhập sai trước khi áp dụng giới hạn',
        ],
        'security_lockout_minutes' => [
            'value' => '15',
            'group' => 'security',
            'type' => 'number',
            'label' => 'Thời gian khóa (phút)',
            'description' => 'Thời gian giới hạn đăng nhập sau nhiều lần sai',
        ],
        'maintenance_mode' => [
            'value' => '0',
            'group' => 'operations',
            'type' => 'boolean',
            'label' => 'Chế độ bảo trì',
            'description' => 'Hiển thị trang bảo trì thay cho website khi được bật',
        ],
        'maintenance_message' => [
            'value' => '',
            'group' => 'operations',
            'type' => 'textarea',
            'label' => 'Thông báo bảo trì',
            'description' => 'Nội dung hiển thị trong thời gian bảo trì',
        ],
        'cache_frontend_enabled' => [
            'value' => '1',
            'group' => 'operations',
            'type' => 'boolean',
            'label' => 'Bật cache frontend',
            'description' => 'Cho phép cache dữ liệu và giao diện frontend',
        ],
        'cache_settings_enabled' => [
            'value' => '1',
            'group' => 'operations',
            'type' => 'boolean',
            'label' => 'Bật cache settings',
            'description' => 'Cho phép lưu cache các cấu hình hệ thống',
        ],
        'operations_log_level' => [
            'value' => 'error',
            'group' => 'operations',
            'type' => 'text',
            'label' => 'Log level',
            'description' => 'Mức log vận hành, ví dụ: emergency, alert, critical, error, warning, info, debug',
        ],
        'operations_debug_enabled' => [
            'value' => '0',
            'group' => 'operations',
            'type' => 'boolean',
            'label' => 'Bật debug',
            'description' => 'Chỉ dùng trong môi trường phát triển; chưa thay đổi APP_DEBUG',
        ],
        'operations_queue_enabled' => [
            'value' => '0',
            'group' => 'operations',
            'type' => 'boolean',
            'label' => 'Bật queue',
            'description' => 'Cho phép xử lý tác vụ nền; hiện chỉ lưu cấu hình',
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::SETTINGS as $key => $definition) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => $definition['value'],
                'group' => $definition['group'],
                'type' => $definition['type'],
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
