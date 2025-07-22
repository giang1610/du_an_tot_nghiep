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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['shipping', 'product']); //  1=shipping, 2=product
            $table->enum('discount_type', ['amount', 'percent']); // Loại giảm giá: amount (số tiền) hoặc percent (phần trăm)
            $table->decimal('discount_amount', 10, 2)->nullable(); //Số tiền giảm cố định.
            $table->decimal('discount_percent', 5, 2)->nullable(); //Phần trăm giảm giá
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('quantity')->nullable(); // Tổng số lượt sử dụng cho toàn hệ thống
            $table->integer('usage_limit')->nullable(); // Số lần tối đa 1 user được sử dụng
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
