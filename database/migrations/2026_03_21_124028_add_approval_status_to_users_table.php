<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'pending' = awaiting admin review
            // 'approved' = admin approved, can login
            // 'rejected' = admin rejected, blocked
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                  ->default('approved') // existing users (agents, customers) stay approved
                  ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};