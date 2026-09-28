<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Mở rộng bảng products: loại sản phẩm, kích thước tách riêng, cấu hình
 * vận chuyển & thuế, chính sách tồn kho/khối lượng đặt hàng.
 * Thêm bảng pivot product_tag.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type', 30)->default('physical')->after('dimensions');
            }
            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 8, 2)->nullable()->after('product_type');
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 8, 2)->nullable()->after('length');
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 8, 2)->nullable()->after('width');
            }
            if (!Schema::hasColumn('products', 'is_free_shipping')) {
                $table->boolean('is_free_shipping')->default(false)->after('height');
            }
            if (!Schema::hasColumn('products', 'shipping_fee')) {
                $table->decimal('shipping_fee', 15, 2)->nullable()->after('is_free_shipping');
            }
            if (!Schema::hasColumn('products', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->nullable()->after('shipping_fee');
            }
            if (!Schema::hasColumn('products', 'is_tax_inclusive')) {
                $table->boolean('is_tax_inclusive')->default(true)->after('tax_rate');
            }
            if (!Schema::hasColumn('products', 'allow_backorder')) {
                $table->boolean('allow_backorder')->default(false)->after('is_tax_inclusive');
            }
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->unsignedInteger('low_stock_threshold')->nullable()->after('allow_backorder');
            }
            if (!Schema::hasColumn('products', 'min_order_quantity')) {
                $table->unsignedInteger('min_order_quantity')->nullable()->after('low_stock_threshold');
            }
            if (!Schema::hasColumn('products', 'max_order_quantity')) {
                $table->unsignedInteger('max_order_quantity')->nullable()->after('min_order_quantity');
            }
            if (!Schema::hasColumn('products', 'sold_individually')) {
                $table->boolean('sold_individually')->default(false)->after('max_order_quantity');
            }
        });

        $this->backfillDimensions();

        if (!Schema::hasTable('product_tag')) {
            Schema::create('product_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'tag_id']);
                $table->index('tag_id');
            });
        }
    }

    /**
     * Tách dimensions "20x15x10" (D x R x C) ra 3 cột riêng.
     * Log các row không match định dạng, không fail migration.
     */
    private function backfillDimensions(): void
    {
        $rows = DB::table('products')
            ->whereNotNull('dimensions')
            ->where('dimensions', '!=', '')
            ->whereNull('length')
            ->select(['id', 'dimensions'])
            ->get();

        foreach ($rows as $row) {
            $value = trim((string) $row->dimensions);

            // Hỗ trợ "20x15x10", "20 x 15 x 10", "20*15*10", "20.5x15.2x10.1"
            if (!preg_match('/^\s*(\d+(?:[.,]\d+)?)\s*[xX*×]\s*(\d+(?:[.,]\d+)?)\s*[xX*×]\s*(\d+(?:[.,]\d+)?)\s*$/', $value, $m)) {
                Log::warning("Migration enhance_products: không parse được dimensions cho product #{$row->id}", [
                    'dimensions' => $value,
                ]);

                continue;
            }

            $dim = array_map(fn ($v) => (float) str_replace(',', '.', $v), [$m[1], $m[2], $m[3]]);

            DB::table('products')
                ->where('id', $row->id)
                ->update([
                    'length' => $dim[0],
                    'width'  => $dim[1],
                    'height' => $dim[2],
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn([
                    'product_type',
                    'length',
                    'width',
                    'height',
                    'is_free_shipping',
                    'shipping_fee',
                    'tax_rate',
                    'is_tax_inclusive',
                    'allow_backorder',
                    'low_stock_threshold',
                    'min_order_quantity',
                    'max_order_quantity',
                    'sold_individually',
                ]);
            });
        }
    }
};
