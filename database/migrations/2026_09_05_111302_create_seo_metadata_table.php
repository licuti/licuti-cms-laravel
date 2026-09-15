<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('locale', 10)->index();
            
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_image')->nullable();
            
            $table->string('canonical_url')->nullable();
            
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            
            $table->string('focus_keyword')->nullable();
            $table->integer('seo_score')->nullable();
            
            $table->timestamps();

            // Ensure one translation per entity per locale
            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'seo_metadata_unique_locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
