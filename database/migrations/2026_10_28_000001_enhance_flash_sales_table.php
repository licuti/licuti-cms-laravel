<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flash_sales', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->dateTime('starts_at')->index()->after('uuid');
            $table->dateTime('ends_at')->index()->after('starts_at');
            $table->boolean('is_active')->default(true)->index()->after('ends_at');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_at');

            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_flash_sales_window');
        });
    }

    public function down(): void
    {
        Schema::table('flash_sales', function (Blueprint $table) {
            $table->dropIndex('idx_flash_sales_window');
            $table->dropForeign(['created_by']);
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'starts_at', 'ends_at', 'is_active', 'created_by']);
        });
    }
};
