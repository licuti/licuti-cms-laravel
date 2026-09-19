<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('products', 'category_id')) {
                    $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('uuid');
                }
                if (!Schema::hasColumn('products', 'brand_id')) {
                    $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete()->after('category_id');
                }
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku', 100)->nullable()->unique()->after('brand_id');
                }
                if (!Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode', 100)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('products', 'price')) {
                    $table->decimal('price', 15, 2)->default(0)->after('barcode');
                }
                if (!Schema::hasColumn('products', 'compare_price')) {
                    $table->decimal('compare_price', 15, 2)->nullable()->after('price');
                }
                if (!Schema::hasColumn('products', 'cost_price')) {
                    $table->decimal('cost_price', 15, 2)->nullable()->after('compare_price');
                }
                if (!Schema::hasColumn('products', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(0)->after('cost_price');
                }
                if (!Schema::hasColumn('products', 'track_inventory')) {
                    $table->boolean('track_inventory')->default(true)->after('stock_quantity');
                }
                if (!Schema::hasColumn('products', 'weight')) {
                    $table->decimal('weight', 8, 2)->nullable()->after('track_inventory');
                }
                if (!Schema::hasColumn('products', 'dimensions')) {
                    $table->string('dimensions', 100)->nullable()->after('weight');
                }
                if (!Schema::hasColumn('products', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('dimensions');
                }
                if (!Schema::hasColumn('products', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_featured');
                }
                if (!Schema::hasColumn('products', 'status')) {
                    $table->string('status', 30)->default('published')->after('is_active');
                }
                if (!Schema::hasColumn('products', 'published_at')) {
                    $table->dateTime('published_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('products', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                if (!Schema::hasColumn('product_translations', 'product_id')) {
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('product_translations', 'locale')) {
                    $table->string('locale', 10)->after('product_id');
                }
                if (!Schema::hasColumn('product_translations', 'name')) {
                    $table->string('name')->after('locale');
                }
                if (!Schema::hasColumn('product_translations', 'slug')) {
                    $table->string('slug')->after('name');
                }
                if (!Schema::hasColumn('product_translations', 'short_description')) {
                    $table->text('short_description')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('product_translations', 'description')) {
                    $table->longText('description')->nullable()->after('short_description');
                }
            });
        }

        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('image');
                $table->boolean('is_primary')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
    }
};
