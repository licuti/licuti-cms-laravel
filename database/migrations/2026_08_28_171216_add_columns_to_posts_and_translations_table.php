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
        // Add columns to posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('status')->default('draft')->after('uuid');
            $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->nullOnDelete()->after('status');
            $table->string('thumbnail_url')->nullable()->after('post_category_id');
            $table->timestamp('published_at')->nullable()->after('thumbnail_url');
            $table->softDeletes();

            $table->index('status');
            $table->index('post_category_id');
        });

        // Add columns to post_translations table
        Schema::table('post_translations', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete()->after('id');
            $table->string('locale', 10)->after('post_id');
            $table->string('title')->after('locale');
            $table->string('slug')->after('title');
            $table->text('excerpt')->nullable()->after('slug');
            $table->longText('content')->nullable()->after('excerpt');
            $table->string('meta_title')->nullable()->after('content');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');

            $table->unique(['post_id', 'locale']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'locale']);
            $table->dropIndex(['slug']);
            $table->dropConstrainedForeignId('post_id');
            $table->dropColumn(['locale', 'title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['status']);
            $table->dropIndex(['post_category_id']);
            $table->dropColumn(['uuid', 'status', 'thumbnail_url', 'published_at']);
            $table->dropConstrainedForeignId('post_category_id');
        });
    }
};
