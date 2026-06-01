<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'attributes_json')) {
                $table->json('attributes_json')->nullable()->after('images');
            }

            if (!Schema::hasColumn('products', 'variations_json')) {
                $table->json('variations_json')->nullable()->after('attributes_json');
            }
        });

        try {
            DB::statement('ALTER TABLE products MODIFY sku VARCHAR(255) NULL');
        } catch (\Throwable $e) {
            // Some database drivers do not support MODIFY; validation already treats SKU as optional.
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'variations_json')) {
                $table->dropColumn('variations_json');
            }

            if (Schema::hasColumn('products', 'attributes_json')) {
                $table->dropColumn('attributes_json');
            }
        });
    }
};
