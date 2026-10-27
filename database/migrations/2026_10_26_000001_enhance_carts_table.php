<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->foreignId('user_id')->nullable()->after('uuid')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('currency', 3)->default('VND');
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('converted_order_id')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'last_activity_at']);
            $table->dropColumn([
                'uuid',
                'user_id',
                'coupon_id',
                'guest_email',
                'currency',
                'status',
                'converted_order_id',
                'last_activity_at',
            ]);
        });
    }
};
