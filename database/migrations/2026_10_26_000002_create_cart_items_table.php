<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('line_key', 40);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price_snapshot', 15, 2);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'line_key']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
