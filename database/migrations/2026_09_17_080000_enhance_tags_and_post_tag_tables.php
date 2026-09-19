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
        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasColumn('tags', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('tags', 'name')) {
                $table->string('name')->nullable()->after('uuid');
            }
            if (!Schema::hasColumn('tags', 'slug')) {
                $table->string('slug')->nullable()->index()->after('name');
            }
            if (!Schema::hasColumn('tags', 'display_order')) {
                $table->integer('display_order')->default(0)->after('slug');
            }
        });

        Schema::table('post_tag', function (Blueprint $table) {
            if (!Schema::hasColumn('post_tag', 'post_id')) {
                $table->foreignId('post_id')->after('id')->constrained('posts')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('post_tag', 'tag_id')) {
                $table->foreignId('tag_id')->after('post_id')->constrained('tags')->cascadeOnDelete();
            }
            $table->unique(['post_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_tag', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropForeign(['tag_id']);
            $table->dropUnique(['post_id', 'tag_id']);
            $table->dropColumn(['post_id', 'tag_id']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['tags_uuid_unique']);
            $table->dropColumn(['uuid', 'name', 'slug', 'display_order']);
        });
    }
};
