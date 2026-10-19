<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('order_number', 50)->unique()->after('uuid');
            $table->foreignId('user_id')->nullable()->after('order_number')->constrained('users')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->after('user_id')->constrained('payment_methods')->nullOnDelete();
            $table->string('status', 30)->default('pending')->after('payment_method_id');
            $table->decimal('subtotal', 15, 2)->default(0)->after('status');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('shipping_fee', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('shipping_fee');
            $table->decimal('total_amount', 15, 2)->default(0)->after('tax_amount');
            $table->string('currency', 10)->default('VND')->after('total_amount');
            $table->text('notes')->nullable()->after('currency');
            $table->text('cancel_reason')->nullable()->after('notes');
            $table->timestamp('paid_at')->nullable()->after('cancel_reason');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('completed_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->softDeletes()->after('updated_at');

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn([
                'uuid',
                'order_number',
                'user_id',
                'payment_method_id',
                'status',
                'subtotal',
                'discount_amount',
                'shipping_fee',
                'tax_amount',
                'total_amount',
                'currency',
                'notes',
                'cancel_reason',
                'paid_at',
                'shipped_at',
                'completed_at',
                'cancelled_at',
                'deleted_at',
            ]);
        });
    }
};
