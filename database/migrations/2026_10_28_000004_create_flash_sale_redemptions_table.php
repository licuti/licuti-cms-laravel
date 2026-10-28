<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_product_id')->constrained('flash_sale_products')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_email', 100)->nullable();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('reserved');
            $table->dateTime('released_at')->nullable();
            $table->timestamps();

            $table->index(['flash_sale_product_id', 'user_id', 'status'], 'idx_redemptions_user');
            $table->index(['flash_sale_product_id', 'guest_email', 'status'], 'idx_redemptions_guest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_redemptions');
    }
};
