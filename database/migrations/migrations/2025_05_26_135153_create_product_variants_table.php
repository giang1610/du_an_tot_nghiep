<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('sku');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->datetime('sale_start_date')->nullable();
            $table->datetime('sale_end_date')->nullable();
            $table->string('image')->nullable();
            $table->integer('stock')->default(0);
            $table->foreignId('color_id')->constrained('colors');
            $table->foreignId('size_id')->constrained('sizes');
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
}
