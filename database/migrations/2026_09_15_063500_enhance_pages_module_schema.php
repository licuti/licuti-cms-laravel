<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Hoàn thiện schema module Page theo pattern Post:
     *  - pages: parent_id (self FK), page_template, image
     *  - page_translations: slug (index), excerpt
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('uuid')
                    ->constrained('pages')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('pages', 'page_template')) {
                $table->string('page_template', 50)->default('default')->after('parent_id')->index();
            }
            if (!Schema::hasColumn('pages', 'image')) {
                $table->string('image')->nullable()->after('page_template');
            }
        });

        Schema::table('page_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('page_translations', 'slug')) {
                $table->string('slug')->nullable()->after('title')->index();
            }
            if (!Schema::hasColumn('page_translations', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }
            // Ensure unique(page_id, locale) exists
            $indexes = Schema::getIndexes('page_translations');
            $hasUnique = collect($indexes)->contains(fn ($i) => $i['name'] === 'page_translations_page_id_locale_unique'
                || ($i['unique'] && collect($i['columns'])->intersect(['page_id', 'locale'])->count() === 2));
            if (!$hasUnique) {
                $table->unique(['page_id', 'locale']);
            }
        });

        // Backfill slug từ title cho các bản ghi cũ
        DB::table('page_translations')->whereNull('slug')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $slug = Str::slug($row->title ?: 'page-' . $row->page_id);
                DB::table('page_translations')->where('id', $row->id)->update(['slug' => $slug]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $indexes = Schema::getIndexes('page_translations');
            $hasUnique = collect($indexes)->contains(fn ($i) => $i['name'] === 'page_translations_page_id_locale_unique');
            if ($hasUnique) {
                $table->dropUnique('page_translations_page_id_locale_unique');
            }
            $table->dropColumn(['slug', 'excerpt']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'page_template', 'image']);
        });
    }
};