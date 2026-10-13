<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->text('instructions')->nullable()->after('description');
            $table->unsignedInteger('display_order')->default(0)->after('is_active');
            $table->decimal('min_order_amount', 15, 2)->nullable()->after('display_order');
            $table->decimal('max_order_amount', 15, 2)->nullable()->after('min_order_amount');
            $table->json('supported_currencies')->nullable()->after('max_order_amount');

            $table->index(['is_active', 'display_order'], 'pm_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropIndex('pm_active_order_idx');
            $table->dropColumn([
                'instructions',
                'display_order',
                'min_order_amount',
                'max_order_amount',
                'supported_currencies',
            ]);
        });
    }
};
