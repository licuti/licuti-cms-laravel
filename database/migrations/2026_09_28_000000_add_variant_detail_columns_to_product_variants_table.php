<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Các cột barcode / cost_price / image đã tồn tại trên DB dev (thêm thủ công
 * qua các đợt trước) nhưng KHÔNG có trong migration nào → môi trường test
 * (RefreshDatabase) và production thiếu. Migration này chính thức hóa chúng.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'barcode')) {
                    $table->string('barcode', 100)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('product_variants', 'cost_price')) {
                    $table->decimal('cost_price', 15, 2)->nullable()->after('compare_price');
                }
                if (!Schema::hasColumn('product_variants', 'image')) {
                    // Media uuid hoặc URL — cùng pattern products.primary_image
                    $table->string('image')->nullable()->after('cost_price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn(['barcode', 'cost_price', 'image']);
            });
        }
    }
};
