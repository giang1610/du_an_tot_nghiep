<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id'); // FK đến product_variants

          
            $table->string('attribute_name');   // Tên thuộc tính (VD: "Màu sắc")
            $table->string('attribute_value');  // Giá trị (VD: "Đỏ")

            $table->timestamps();

            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variant_options');
    }
}
