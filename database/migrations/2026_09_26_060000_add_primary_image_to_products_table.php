<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'primary_image')) {
            Schema::table('products', function (Blueprint $table) {
                // Cùng pattern với brands.logo: media uuid hoặc URL
                $table->string('primary_image')->nullable()->after('dimensions');
            });
        }

        // Backfill từ product_images: row is_primary đầu tiên, hoặc display_order đầu tiên
        if (Schema::hasTable('product_images')) {
            $products = DB::table('products')->whereNull('primary_image')->pluck('id');

            foreach ($products as $productId) {
                $primary = DB::table('product_images')
                    ->where('product_id', $productId)
                    ->orderByRaw('CASE WHEN is_primary = 1 THEN 0 ELSE 1 END')
                    ->orderBy('display_order')
                    ->orderBy('id')
                    ->value('image');

                if (!empty($primary)) {
                    DB::table('products')->where('id', $productId)->update(['primary_image' => $primary]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'primary_image')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('primary_image');
            });
        }
    }
};
