<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            // $table->foreignId('product_variant_option_id')->nullable()->constrained('product_variant_options')->onDelete('cascade');
            $table->string('sku', 255)->nullable(false); // Không null nếu yêu cầu
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('sale_price', 10, 2)->unsigned()->nullable();
            $table->integer('stock')->unsigned()->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
