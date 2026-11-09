<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('flash_sale_product_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('flash_sale_products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['flash_sale_product_id']);
            $table->dropColumn('flash_sale_product_id');
        });
    }
};
