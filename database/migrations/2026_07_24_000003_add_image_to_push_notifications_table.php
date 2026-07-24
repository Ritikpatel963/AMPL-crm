<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('body');
            $table->string('target_label')->nullable()->after('target'); // human-readable label for history
        });
    }

    public function down(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'target_label']);
        });
    }
};
