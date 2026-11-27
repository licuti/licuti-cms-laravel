<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TO_APPEARANCE = [
        'logo_url',
        'favicon_url',
    ];

    private const TO_GENERAL = [
        'footer_copyright',
    ];

    public function up(): void
    {
        DB::table('settings')
            ->whereIn('key', self::TO_APPEARANCE)
            ->update(['group' => 'appearance', 'updated_at' => now()]);

        DB::table('settings')
            ->whereIn('key', self::TO_GENERAL)
            ->update(['group' => 'general', 'updated_at' => now()]);

        Cache::forget('app_settings');
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', self::TO_APPEARANCE)
            ->update(['group' => 'general', 'updated_at' => now()]);

        DB::table('settings')
            ->whereIn('key', self::TO_GENERAL)
            ->update(['group' => 'appearance', 'updated_at' => now()]);

        Cache::forget('app_settings');
    }
};
