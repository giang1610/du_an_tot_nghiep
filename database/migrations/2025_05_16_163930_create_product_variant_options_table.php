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
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_variant_id'); // FK đến product_variants
            $table->string('attribute_name', 255); // Tên thuộc tính (VD: Màu sắc)
            $table->string('attribute_value', 255); // Giá trị thuộc tính (VD: Đỏ)

            $table->timestamps();

            // Khóa ngoại
            $table->foreign('product_variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
    }
};
