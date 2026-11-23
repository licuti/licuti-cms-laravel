<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $unresolved = DB::table('posts as posts')
            ->leftJoin('media', 'media.uuid', '=', 'posts.image')
            ->whereNotNull('posts.image')
            ->where('posts.image', '<>', '')
            ->whereNull('media.id')
            ->exists();

        if ($unresolved) {
            throw new RuntimeException('Cannot migrate posts.image: unresolved legacy image values remain.');
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('image_media_id')
                ->nullable()
                ->after('image')
                ->constrained('media')
                ->restrictOnDelete();
        });

        DB::statement('
            UPDATE posts
            INNER JOIN media ON media.uuid = posts.image
            SET posts.image_media_id = media.id
            WHERE posts.image IS NOT NULL AND posts.image <> ""
        ');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image')->nullable()->after('author_id');
        });

        DB::statement('
            UPDATE posts
            INNER JOIN media ON media.id = posts.image_media_id
            SET posts.image = media.uuid
            WHERE posts.image_media_id IS NOT NULL
        ');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['image_media_id']);
            $table->dropColumn('image_media_id');
        });
    }
};
