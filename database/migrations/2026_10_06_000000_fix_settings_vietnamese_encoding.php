<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix Vietnamese mojibake stored in `settings` (UTF-8 bytes once
     * mis-interpreted as Windows-1252/latin1 then re-encoded as UTF-8)
     * and backfill the missing `mail` group rows.
     */
    public function up(): void
    {
        $fixes = [
            'site_name' => [
                'description' => 'Tên hiển thị trên tiêu đề trình duyệt và trang chủ',
            ],
            'favicon_url' => [
                'description' => 'Icon nhỏ trên tab trình duyệt',
            ],
            'contact_address' => [
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ văn phòng / cửa hàng chính',
            ],
            'footer_copyright' => [
                'description' => 'Dòng bản quyền dưới cùng trang web',
            ],
        ];

        foreach ($fixes as $key => $columns) {
            DB::table('settings')->where('key', $key)->update(array_merge(
                $columns,
                ['updated_at' => now()]
            ));
        }

        $mailDefaults = [
            ['key' => 'mail_mailer', 'value' => 'smtp', 'type' => 'text', 'label' => 'Mail Mailer', 'description' => 'Giao thức gửi mail (smtp, mailgun, ses...)'],
            ['key' => 'mail_host', 'value' => 'sandbox.smtp.mailtrap.io', 'type' => 'text', 'label' => 'Mail Host', 'description' => 'Địa chỉ máy chủ SMTP'],
            ['key' => 'mail_port', 'value' => '2525', 'type' => 'number', 'label' => 'Mail Port', 'description' => 'Cổng kết nối SMTP (25, 465, 587, 2525)'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'text', 'label' => 'Mail Username', 'description' => 'Tài khoản đăng nhập SMTP'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'text', 'label' => 'Mail Password', 'description' => 'Mật khẩu ứng dụng SMTP'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'text', 'label' => 'Mail Encryption', 'description' => 'Giao thức mã hóa (tls, ssl)'],
            ['key' => 'mail_from_address', 'value' => 'no-reply@licuti.com', 'type' => 'text', 'label' => 'Mail From Address', 'description' => 'Email gửi mặc định'],
            ['key' => 'mail_from_name', 'value' => 'Licuti System', 'type' => 'text', 'label' => 'Mail From Name', 'description' => 'Tên người gửi mặc định'],
        ];

        foreach ($mailDefaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, [
                    'group' => 'mail',
                    'is_translatable' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        // Data repair only — nothing to revert.
    }
};
