<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Xoá 3 cột meta_title/meta_description/meta_keywords trên bảng `pages`.
 *
 * Bối cảnh: từ khi HasSeo trait tách SEO về bảng `seo_metadata` (per-locale,
 * morphMany), các cột này chỉ còn bị PageDTO::toArray() ghi NULL mỗi lần save —
 * dead data trùng lặp với seo_metadata. Module Page nhập SEO từ
 * <x-admin.seo-meta> với name="translations[xx][meta_*]" → không đụng cột cũ.
 *
 * An toàn: backfill giá trị cũ (nếu còn) sang seo_metadata của default locale
 * TRƯỚC khi drop — chỉ điền vào vị trí đang trống (không ghi đè dữ liệu SEO mới).
 */
return new class extends Migration
{
    private const SEO_TYPE = 'App\\Models\\Page';

    public function up(): void
    {
        $locale = DB::table('languages')->where('is_default', true)->value('code') ?: 'vi';

        DB::table('pages')
            ->where(function ($q) {
                $q->where(function ($x) { $x->whereNotNull('meta_title')->where('meta_title', '<>', ''); })
                  ->orWhere(function ($x) { $x->whereNotNull('meta_description')->where('meta_description', '<>', ''); })
                  ->orWhere(function ($x) { $x->whereNotNull('meta_keywords')->where('meta_keywords', '<>', ''); });
            })
            ->orderBy('id')
            ->each(function ($page) use ($locale) {
                $seo = DB::table('seo_metadata')
                    ->where('seoable_type', self::SEO_TYPE)
                    ->where('seoable_id', $page->id)
                    ->where('locale', $locale)
                    ->first();

                if (!$seo) {
                    DB::table('seo_metadata')->insert([
                        'seoable_type'     => self::SEO_TYPE,
                        'seoable_id'       => $page->id,
                        'locale'           => $locale,
                        'meta_title'       => $page->meta_title,
                        'meta_description' => $page->meta_description,
                        'meta_keywords'    => $page->meta_keywords,
                        'robots_index'     => 1,
                        'robots_follow'    => 1,
                        'seo_score'        => 0,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);

                    return;
                }

                $fill = [];
                foreach (['meta_title', 'meta_description', 'meta_keywords'] as $col) {
                    if (($seo->{$col} === null || $seo->{$col} === '')
                        && $page->{$col} !== null && $page->{$col} !== '') {
                        $fill[$col] = $page->{$col};
                    }
                }

                if ($fill) {
                    DB::table('seo_metadata')
                        ->where('id', $seo->id)
                        ->update([...$fill, 'updated_at' => now()]);
                }
            });

        Schema::table('pages', function (Blueprint $table) {
            foreach (['meta_title', 'meta_description', 'meta_keywords'] as $col) {
                if (Schema::hasColumn('pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('pages', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('pages', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }
        });

        $locale = DB::table('languages')->where('is_default', true)->value('code') ?: 'vi';

        // Best-effort restore từ seo_metadata của default locale.
        DB::table('seo_metadata')
            ->where('seoable_type', self::SEO_TYPE)
            ->where('locale', $locale)
            ->orderBy('id')
            ->each(function ($seo) {
                DB::table('pages')
                    ->where('id', $seo->seoable_id)
                    ->update([
                        'meta_title'       => $seo->meta_title,
                        'meta_description' => $seo->meta_description,
                        'meta_keywords'    => $seo->meta_keywords,
                    ]);
            });
    }
};
