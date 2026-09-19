<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (!Schema::hasColumn('banners', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('banners', 'position')) {
                    $table->string('position', 50)->default('home_slider')->after('uuid');
                }
                if (!Schema::hasColumn('banners', 'link')) {
                    $table->string('link', 255)->nullable()->after('position');
                }
                if (!Schema::hasColumn('banners', 'target')) {
                    $table->string('target', 20)->default('_self')->after('link');
                }
                if (!Schema::hasColumn('banners', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('target');
                }
                if (!Schema::hasColumn('banners', 'start_date')) {
                    $table->dateTime('start_date')->nullable()->after('display_order');
                }
                if (!Schema::hasColumn('banners', 'end_date')) {
                    $table->dateTime('end_date')->nullable()->after('start_date');
                }
                if (!Schema::hasColumn('banners', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('end_date');
                }
                if (!Schema::hasColumn('banners', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if (Schema::hasTable('banner_translations')) {
            Schema::table('banner_translations', function (Blueprint $table) {
                if (!Schema::hasColumn('banner_translations', 'banner_id')) {
                    $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('banner_translations', 'locale')) {
                    $table->string('locale', 10)->after('banner_id');
                }
                if (!Schema::hasColumn('banner_translations', 'title')) {
                    $table->string('title')->after('locale');
                }
                if (!Schema::hasColumn('banner_translations', 'image')) {
                    $table->string('image')->nullable()->after('title');
                }
                if (!Schema::hasColumn('banner_translations', 'description')) {
                    $table->text('description')->nullable()->after('image');
                }
            });
        }
    }

    public function down(): void
    {
    }
};
