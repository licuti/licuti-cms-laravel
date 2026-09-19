<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('brands', 'logo')) {
                $table->string('logo')->nullable()->after('uuid');
            }
            if (!Schema::hasColumn('brands', 'website')) {
                $table->string('website')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('brands', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('website');
            }
            if (!Schema::hasColumn('brands', 'display_order')) {
                $table->integer('display_order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('brands', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('brand_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('brand_translations', 'brand_id')) {
                $table->foreignId('brand_id')->after('id')->constrained('brands')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('brand_translations', 'locale')) {
                $table->string('locale', 10)->after('brand_id');
            }
            if (!Schema::hasColumn('brand_translations', 'name')) {
                $table->string('name')->after('locale');
            }
            if (!Schema::hasColumn('brand_translations', 'slug')) {
                $table->string('slug')->after('name');
            }
            if (!Schema::hasColumn('brand_translations', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            $table->unique(['brand_id', 'locale']);
            $table->index(['locale', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_translations', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropUnique(['brand_translations_brand_id_locale_unique']);
            $table->dropIndex(['brand_translations_locale_slug_index']);
            $table->dropColumn(['brand_id', 'locale', 'name', 'slug', 'description']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['brands_uuid_unique']);
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'logo', 'website', 'is_active', 'display_order']);
        });
    }
};
