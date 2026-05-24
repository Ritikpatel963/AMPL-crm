<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('adminusers');
    }

    public function down(): void
    {
        Schema::create('adminusers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['agent', 'subadmin'])->default('agent');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }
};
