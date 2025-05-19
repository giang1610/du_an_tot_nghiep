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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID sản phẩm - PK, AUTO_INCREMENT
            $table->unsignedBigInteger('category_id'); // FK đến categories
            $table->string('name', 255); // Tên sản phẩm - NOT NULL
            $table->string('slug', 255)->unique(); // URL sản phẩm - UNIQUE
            $table->tinyInteger('type')->default(0); // 0: đơn giản, 1: có biến thể
            $table->tinyInteger('status')->default(0); // 0: bản nháp, 1: hiển thị, 2: lưu trữ
            $table->timestamps();

            // Thiết lập khóa ngoại
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
