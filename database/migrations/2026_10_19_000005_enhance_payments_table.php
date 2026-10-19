<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->foreignId('order_id')->after('uuid')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->after('order_id')->constrained('payment_methods')->nullOnDelete();
            $table->string('gateway_code', 30)->after('payment_method_id');
            $table->string('gateway_transaction_id', 100)->nullable()->after('gateway_code');
            $table->string('status', 30)->default('pending')->after('gateway_transaction_id');
            $table->decimal('amount', 15, 2)->after('status');
            $table->string('currency', 10)->default('VND')->after('amount');
            $table->json('gateway_response')->nullable()->after('currency');
            $table->timestamp('paid_at')->nullable()->after('gateway_response');

            $table->index(['gateway_code', 'gateway_transaction_id']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_code', 'gateway_transaction_id']);
            $table->dropIndex(['order_id', 'status']);
            $table->dropForeign(['order_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn([
                'uuid',
                'order_id',
                'payment_method_id',
                'gateway_code',
                'gateway_transaction_id',
                'status',
                'amount',
                'currency',
                'gateway_response',
                'paid_at',
            ]);
        });
    }
};
