<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng pages & page_translations trong DB chỉ có id/timestamps (dump SQL schema cũ).
     * Thêm toàn bộ cột còn thiếu khớp với migration gốc + quy chuẩn ContentStatus.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'uuid')) {
                $table->uuid('uuid')->unique()->after('id');
            }
            if (!Schema::hasColumn('pages', 'display_order')) {
                $table->integer('display_order')->default(0)->after('uuid');
            }
            if (!Schema::hasColumn('pages', 'status')) {
                $table->string('status', 20)->default('draft')->index()->after('display_order');
            }
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

        Schema::table('page_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('page_translations', 'page_id')) {
                $table->foreignId('page_id')->constrained('pages')->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('page_translations', 'locale')) {
                $table->string('locale', 10)->after('page_id');
            }
            if (!Schema::hasColumn('page_translations', 'title')) {
                $table->string('title')->after('locale');
            }
            if (!Schema::hasColumn('page_translations', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }

            // Unique index sau khi có cả 2 cột
            $indexes = Schema::getIndexes('page_translations');
            $hasUnique = collect($indexes)->contains(fn ($i) => $i['unique'] && in_array('page_id', $i['columns']) && in_array('locale', $i['columns']));
            if (!Schema::hasColumn('page_translations', 'page_id')
                && !Schema::hasColumn('page_translations', 'locale')) {
                // nothing to do
            } elseif (!$hasUnique) {
                $table->unique(['page_id', 'locale']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['pages_uuid_unique']); // nullable local
            $table->dropColumn(['uuid', 'display_order', 'status', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropUnique(['page_translations_page_id_locale_unique']);
            $table->dropForeign(['page_id']);
            $table->dropColumn(['page_id', 'locale', 'title', 'content']);
        });
    }
};