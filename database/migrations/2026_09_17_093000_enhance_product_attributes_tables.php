<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('product_attributes', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('product_attributes', 'code')) {
                    $table->string('code', 100)->nullable()->unique()->after('uuid');
                }
                if (!Schema::hasColumn('product_attributes', 'type')) {
                    $table->string('type', 30)->default('select')->after('code');
                }
                if (!Schema::hasColumn('product_attributes', 'is_filterable')) {
                    $table->boolean('is_filterable')->default(true)->after('type');
                }
                if (!Schema::hasColumn('product_attributes', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('is_filterable');
                }
            });
        }

        if (Schema::hasTable('product_attribute_translations')) {
            Schema::table('product_attribute_translations', function (Blueprint $table) {
                if (!Schema::hasColumn('product_attribute_translations', 'attribute_id')) {
                    $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('product_attribute_translations', 'locale')) {
                    $table->string('locale', 10)->after('attribute_id');
                }
                if (!Schema::hasColumn('product_attribute_translations', 'name')) {
                    $table->string('name')->after('locale');
                }
            });
        }

        if (!Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
                $table->string('value');
                $table->string('color_code', 50)->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
