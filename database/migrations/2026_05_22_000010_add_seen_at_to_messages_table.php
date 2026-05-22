<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'seen_at')) {
                $table->timestamp('seen_at')->nullable()->after('data')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'seen_at')) {
                $table->dropIndex(['seen_at']);
                $table->dropColumn('seen_at');
            }
        });
    }
};
