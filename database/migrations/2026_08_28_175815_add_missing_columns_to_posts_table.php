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
        Schema::table('posts', function (Blueprint $table) {
            // Rename columns to match spec in docs/03-database-details.md
            $table->renameColumn('post_category_id', 'category_id');
            $table->renameColumn('thumbnail_url', 'featured_image');

            // Add missing columns per spec
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete()->after('category_id');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->unsignedInteger('view_count')->default(0)->after('is_featured');

            // Indexes
            $table->index('author_id');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['is_featured']);
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn(['is_featured', 'view_count']);
            $table->renameColumn('featured_image', 'thumbnail_url');
            $table->renameColumn('category_id', 'post_category_id');
        });
    }
};
