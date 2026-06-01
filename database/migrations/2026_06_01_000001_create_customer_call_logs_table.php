<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone_number', 30);
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');
            $table->enum('status', ['initiated', 'connected', 'not_connected', 'failed'])->default('initiated');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('recording_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'started_at']);
            $table->index(['agent_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_call_logs');
    }
};
