<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColorProductTable extends Migration
{
    public function up()
    {
        Schema::create('color_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Nếu muốn tránh trùng lặp, thêm unique composite key:
            $table->unique(['product_id', 'color_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('color_product');
    }
}
