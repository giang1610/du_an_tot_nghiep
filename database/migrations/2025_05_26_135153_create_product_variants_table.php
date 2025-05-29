<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id(); // ID biến thể
            $table->unsignedBigInteger('product_id'); // FK đến bảng products

            $table->string('sku', 255); // Mã SKU
            $table->decimal('price', 10, 2); // Giá gốc
            $table->decimal('sale_price', 10, 2)->nullable(); // Giá khuyến mãi

            // Ngày bắt đầu và kết thúc giảm giá
            $table->dateTime('sale_start_date')->nullable();
            $table->dateTime('sale_end_date')->nullable();

            $table->integer('stock')->default(0); // Số lượng tồn kho

            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Đảm bảo SKU là duy nhất theo product_id
            $table->unique(['product_id', 'sku']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
}
