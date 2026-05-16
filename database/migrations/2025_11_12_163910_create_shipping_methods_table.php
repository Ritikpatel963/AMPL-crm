<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('shipping_methods', function (Blueprint $table) {
        $table->id();
        $table->string('method_name');
        $table->decimal('cost', 8, 2);
        $table->string('delivery_time');
        $table->enum('status', ['Active', 'Inactive'])->default('Active');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};