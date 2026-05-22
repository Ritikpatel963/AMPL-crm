<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_stage_transitions')) {
            return;
        }

        Schema::create('lead_stage_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_stage_id')->constrained('lead_stages')->cascadeOnDelete();
            $table->foreignId('to_stage_id')->constrained('lead_stages')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['from_stage_id', 'to_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_stage_transitions');
    }
};
