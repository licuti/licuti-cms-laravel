<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Page model dùng SoftDeletes trait — cần cột deleted_at.
     * (Bảng pages ban đầu dump SQL không có cột này.)
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'deleted_at')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};