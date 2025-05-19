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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->string('url', 255); // Đường dẫn hình ảnh

            $table->unsignedBigInteger('product_id'); // FK đến sản phẩm
            $table->unsignedBigInteger('product_variant_id')->nullable(); // FK đến biến thể (cho phép NULL)

            $table->tinyInteger('is_default')->default(0); // Có phải ảnh mặc định
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
