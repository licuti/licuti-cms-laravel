<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // product_translations: dedupe (product_id, locale) giữ lại row có id nhỏ nhất,
        // sau đó thêm unique constraint. DB thật import từ dump có thể có bản trùng.
        if (Schema::hasTable('product_translations')) {
            DB::table('product_translations')
                ->whereNotIn('id', function ($query) {
                    // Bọc trong derived table: MySQL không cho DELETE từ bảng đang SELECT (lỗi 1093)
                    $query->select('id')->fromSub(function ($sub) {
                        $sub->selectRaw('MIN(id) AS id')
                            ->from('product_translations')
                            ->groupBy('product_id', 'locale');
                    }, 'keep');
                })
                ->delete();

            Schema::table('product_translations', function (Blueprint $table) {
                $table->unique(['product_id', 'locale'], 'product_translations_product_locale_unique');
                $table->index('slug', 'product_translations_slug_index');
                $table->index('locale', 'product_translations_locale_index');
            });
        }

        // product_attribute_translations: dedupe (attribute_id, locale) + unique
        if (Schema::hasTable('product_attribute_translations')) {
            DB::table('product_attribute_translations')
                ->whereNotIn('id', function ($query) {
                    $query->select('id')->fromSub(function ($sub) {
                        $sub->selectRaw('MIN(id) AS id')
                            ->from('product_attribute_translations')
                            ->groupBy('attribute_id', 'locale');
                    }, 'keep');
                })
                ->delete();

            Schema::table('product_attribute_translations', function (Blueprint $table) {
                $table->unique(['attribute_id', 'locale'], 'pat_attribute_locale_unique');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('status', 'products_status_index');
                $table->index('is_featured', 'products_is_featured_index');
            });
        }

        if (Schema::hasTable('product_attribute_values')) {
            Schema::table('product_attribute_values', function (Blueprint $table) {
                $table->index('attribute_id', 'pav_attribute_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_translations')) {
            Schema::table('product_translations', function (Blueprint $table) {
                $table->dropIndex('product_translations_slug_index');
                $table->dropIndex('product_translations_locale_index');
                $table->dropUnique('product_translations_product_locale_unique');
            });
        }

        if (Schema::hasTable('product_attribute_translations')) {
            Schema::table('product_attribute_translations', function (Blueprint $table) {
                $table->dropUnique('pat_attribute_locale_unique');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_status_index');
                $table->dropIndex('products_is_featured_index');
            });
        }

        if (Schema::hasTable('product_attribute_values')) {
            Schema::table('product_attribute_values', function (Blueprint $table) {
                $table->dropIndex('pav_attribute_id_index');
            });
        }
    }
};
