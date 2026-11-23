<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme_slug', 100);
            $table->string('key', 150);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['theme_slug', 'key']);
            $table->index('theme_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
