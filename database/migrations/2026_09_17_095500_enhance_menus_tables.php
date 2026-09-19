<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table) {
                if (!Schema::hasColumn('menus', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('menus', 'name')) {
                    $table->string('name')->after('uuid');
                }
                if (!Schema::hasColumn('menus', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }
                if (!Schema::hasColumn('menus', 'location')) {
                    $table->string('location', 50)->nullable()->after('slug');
                }
                if (!Schema::hasColumn('menus', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('location');
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table) {
                if (!Schema::hasColumn('menu_items', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('menu_items', 'menu_id')) {
                    $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete()->after('uuid');
                }
                if (!Schema::hasColumn('menu_items', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete()->after('menu_id');
                }
                if (!Schema::hasColumn('menu_items', 'title')) {
                    $table->string('title')->after('parent_id');
                }
                if (!Schema::hasColumn('menu_items', 'url')) {
                    $table->string('url')->default('/')->after('title');
                }
                if (!Schema::hasColumn('menu_items', 'target')) {
                    $table->string('target', 20)->default('_self')->after('url');
                }
                if (!Schema::hasColumn('menu_items', 'icon')) {
                    $table->string('icon', 100)->nullable()->after('target');
                }
                if (!Schema::hasColumn('menu_items', 'type')) {
                    $table->string('type', 50)->default('custom')->after('icon');
                }
                if (!Schema::hasColumn('menu_items', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->after('type');
                }
                if (!Schema::hasColumn('menu_items', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('reference_id');
                }
            });
        }
    }

    public function down(): void
    {
    }
};
