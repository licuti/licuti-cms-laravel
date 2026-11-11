<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_key', 128);
            $table->string('recipient_key', 128);
            $table->string('channel', 16);
            $table->string('event_type', 64);
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->string('status', 16)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at', 6)->nullable();
            $table->uuid('lease_token')->nullable();
            $table->timestamp('lease_expires_at', 6)->nullable();
            $table->timestamp('last_enqueued_at', 6)->nullable();
            $table->timestamp('sent_at', 6)->nullable();
            $table->timestamp('finished_at', 6)->nullable();
            $table->string('last_error_code', 100)->nullable();
            $table->timestamps(6);

            $table->unique(['event_key', 'recipient_key', 'channel'], 'notification_deliveries_event_recipient_channel_unique');
            $table->index(['status', 'available_at'], 'notification_deliveries_pending_index');
            $table->index(['status', 'lease_expires_at'], 'notification_deliveries_lease_index');
            $table->index(['status', 'finished_at'], 'notification_deliveries_retention_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
