<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'phone')) {
                $table->string('phone', 20)->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('admins', 'is_main_admin')) {
                $table->boolean('is_main_admin')->default(false)->after('password');
            }

            if (!Schema::hasColumn('admins', 'remember_token')) {
                $table->rememberToken()->after('is_main_admin');
            }
        });

        DB::table('admins')->updateOrInsert(
            ['phone' => '9630884927'],
            [
                'name' => 'Main Admin',
                'email' => '9630884927@admin.local',
                'password' => Hash::make('password'),
                'is_main_admin' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('admins', 'is_main_admin')) {
                $table->dropColumn('is_main_admin');
            }

            if (Schema::hasColumn('admins', 'phone')) {
                $table->dropUnique(['phone']);
                $table->dropColumn('phone');
            }
        });
    }
};
