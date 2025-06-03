<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // bigint, PK, AUTO_INCREMENT
            $table->unsignedBigInteger('category_id'); // FK đến categories
            $table->string('name', 255); // NOT NULL
            $table->string('slug', 255)->unique(); // UNIQUE
            $table->tinyInteger('type')->default(0); // 0: đơn giản, 1: có biến thể
            $table->tinyInteger('status')->default(0); // 0: bản nháp, 1: hiển thị, 2: lưu trữ

            // Các trường bạn yêu cầu thêm:
            $table->text('short_description')->nullable(); // Mô tả ngắn
            $table->longText('description')->nullable(); // Mô tả chi tiết
            $table->string('thumbnail')->nullable(); // Ảnh đại diện

            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
