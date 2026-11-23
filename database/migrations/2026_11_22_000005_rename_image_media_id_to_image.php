<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameImageColumn('posts');
        $this->renameImageColumn('content_blocks');
    }

    public function down(): void
    {
        $this->renameImageColumnBack('content_blocks');
        $this->renameImageColumnBack('posts');
    }

    private function renameImageColumn(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'image_media_id') || Schema::hasColumn($tableName, 'image')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropForeign(['image_media_id']);
            $table->renameColumn('image_media_id', 'image');
        });

        Schema::table($tableName, function (Blueprint $table) {
            $table->foreign('image')->references('id')->on('media')->restrictOnDelete();
        });
    }

    private function renameImageColumnBack(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'image') || Schema::hasColumn($tableName, 'image_media_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropForeign(['image']);
            $table->renameColumn('image', 'image_media_id');
        });

        Schema::table($tableName, function (Blueprint $table) {
            $table->foreign('image_media_id')->references('id')->on('media')->restrictOnDelete();
        });
    }
};
