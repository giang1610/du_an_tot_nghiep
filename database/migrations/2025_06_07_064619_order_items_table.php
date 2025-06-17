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
        Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
    $table->integer('quantity');
    $table->unsignedBigInteger('color_id')->nullable();
    $table->unsignedBigInteger('size_id')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
