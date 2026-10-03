<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop cột legacy `products.dimensions`.
 *
 * Dữ liệu đã được backfill sang `length`/`width`/`height` ở migration
 * `2026_09_28_010000_enhance_products_for_shipping_taxonomy`. Form không còn
 * field nào submit `dimensions`.
 *
 * Lưu ý: `down()` thêm lại cột rỗng — không thể khôi phục dữ liệu cũ.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'dimensions')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('dimensions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'dimensions')) {
            Schema::table('products', function (Blueprint $table) {
                // Chỉ khôi phục cấu trúc cột, dữ liệu cũ đã mất (đã backfill
                // sang length/width/height).
                $table->string('dimensions', 100)->nullable()->after('weight');
            });
        }
    }
};
