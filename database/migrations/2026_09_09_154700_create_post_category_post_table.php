<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the pivot table
        Schema::create('post_category_post', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('post_category_id')->constrained('post_categories')->cascadeOnDelete();
            
            $table->unique(['post_id', 'post_category_id']);
        });

        // 2. Migrate existing data from posts.category_id to the pivot table
        $posts = DB::table('posts')->whereNotNull('category_id')->get(['id', 'category_id']);
        foreach ($posts as $post) {
            DB::table('post_category_post')->insert([
                'post_id' => $post->id,
                'post_category_id' => $post->category_id,
            ]);
        }

        // 3. Drop the category_id column from posts table
        Schema::table('posts', function (Blueprint $table) {
            // Because the column was originally post_category_id and then renamed,
            // the foreign key name is likely the original one.
            $table->dropForeign('posts_post_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        // 1. Add category_id back to posts table
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('post_categories')->nullOnDelete()->after('status');
        });

        // 2. We cannot reliably restore data from pivot back to single category_id
        // (If a post had multiple, we don't know which one was primary).
        // This is a known limitation of the rollback.

        // 3. Drop the pivot table
        Schema::dropIfExists('post_category_post');
    }
};
