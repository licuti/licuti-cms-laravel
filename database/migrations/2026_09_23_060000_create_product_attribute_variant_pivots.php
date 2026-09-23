<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // M1 — product_attributes: thêm cột product_id (NULL = catalog toàn cục)
        if (Schema::hasTable('product_attributes') && !Schema::hasColumn('product_attributes', 'product_id')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->nullable()
                    ->constrained('products')
                    ->cascadeOnDelete()
                    ->after('id');
                $table->index('product_id');
            });
        }

        // M2 — pivot product_attribute (product ↔ attribute)
        if (!Schema::hasTable('product_attribute')) {
            Schema::create('product_attribute', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
                $table->boolean('is_variation')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'attribute_id']);
            });
        }

        // M3 — pivot product_attribute_value (product ↔ attribute_value)
        if (!Schema::hasTable('product_attribute_value')) {
            Schema::create('product_attribute_value', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'attribute_value_id']);
            });
        }

        // M4 — pivot product_variant_attribute_values (variant ↔ attribute_value)
        if (!Schema::hasTable('product_variant_attribute_values')) {
            Schema::create('product_variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['variant_id', 'attribute_value_id'], 'pvav_variant_value_unique');
            });
        }

        // M5 — product_variants: nâng cấp từ shell rỗng
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'uuid')) {
                    $table->uuid('uuid')->unique()->after('id');
                }
                if (!Schema::hasColumn('product_variants', 'product_id')) {
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->after('uuid');
                }
                if (!Schema::hasColumn('product_variants', 'sku')) {
                    $table->string('sku', 100)->nullable()->unique()->after('product_id');
                }
                if (!Schema::hasColumn('product_variants', 'price')) {
                    $table->decimal('price', 15, 2)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('product_variants', 'compare_price')) {
                    $table->decimal('compare_price', 15, 2)->nullable()->after('price');
                }
                if (!Schema::hasColumn('product_variants', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(0)->after('compare_price');
                }
                if (!Schema::hasColumn('product_variants', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('stock_quantity');
                }
                if (!Schema::hasColumn('product_variants', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_attribute_value');
        Schema::dropIfExists('product_attribute');

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn([
                    'uuid',
                    'product_id',
                    'sku',
                    'price',
                    'compare_price',
                    'stock_quantity',
                    'is_active',
                    'display_order',
                ]);
            });
        }

        if (Schema::hasTable('product_attributes') && Schema::hasColumn('product_attributes', 'product_id')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
                $table->dropIndex(['product_id']);
                $table->dropColumn('product_id');
            });
        }
    }
};
