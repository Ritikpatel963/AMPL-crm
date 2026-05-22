.
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
        if (!Schema::hasTable('vendor_products')) {
            Schema::create('vendor_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
                $table->string('product_name');
                $table->json('images')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('brand_name')->nullable();
                $table->string('unit_type')->nullable();
                $table->string('unit_size')->nullable();
                $table->decimal('product_rate', 10, 2)->default(0);
                $table->date('product_expiry')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_products');
    }
};
