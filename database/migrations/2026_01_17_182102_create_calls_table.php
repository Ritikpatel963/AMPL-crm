<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();

            $table->string('channel_name')->unique();
            $table->string('agora_uid_caller')->nullable();
            $table->string('agora_uid_receiver')->nullable();

            $table->enum('status', [
                'calling',
                'ringing',
                'connected',
                'ended',
                'missed',
                'rejected',
                'failed'
            ])->default('calling');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable(); // seconds

            // Recording
            $table->boolean('is_recorded')->default(true);
            $table->string('recording_sid')->nullable();
            $table->string('recording_resource_id')->nullable();
            $table->string('recording_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};