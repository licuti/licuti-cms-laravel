<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đổi unique(code) toàn cục thành unique(code, product_id) để các
     * attribute custom của từng product được phép dùng code trùng nhau
     * và trùng với global.
     *
     * An toàn cho data sẵn có: mọi row hiện tại đều có code khác nhau đôi một
     * (do index cũ toàn cục) → vẫn thỏa mãn unique kép.
     *
     * Lưu ý MySQL: row global có product_id NULL, và NULL không so sánh bằng
     * nhau trong unique index → 2 global cùng code vẫn được DB chấp nhận.
     * Trường hợp đó tiếp tục do app-level validation (Store/Update
     * ProductAttributeRequest) chặn.
     */
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropUnique('product_attributes_code_unique');
            $table->unique(['code', 'product_id'], 'product_attributes_code_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropUnique('product_attributes_code_product_unique');
            $table->unique('code', 'product_attributes_code_unique');
        });
    }
};
