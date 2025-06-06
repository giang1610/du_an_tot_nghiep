<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImagesTable extends Migration
{
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255); // Đường dẫn hình ảnh

            $table->unsignedBigInteger('product_id')->nullable();         // FK đến products
            $table->unsignedBigInteger('product_variant_id')->nullable(); // FK đến product_variants

            $table->tinyInteger('is_default')->default(0); // 0: không mặc định, 1: mặc định
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_images');
    }
}

