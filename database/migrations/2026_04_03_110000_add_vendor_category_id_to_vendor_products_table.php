<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            // Add vendor_category_id column
            $table->unsignedBigInteger('vendor_category_id')->nullable()->after('category_id');
            
            // Add foreign key constraint
            $table->foreign('vendor_category_id')
                ->references('id')
                ->on('vendor_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropForeign(['vendor_category_id']);
            $table->dropColumn('vendor_category_id');
        });
    }
};
