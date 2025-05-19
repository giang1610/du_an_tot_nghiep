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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // bigint, PK, AUTO_INCREMENT
            $table->string('name', 255); // NOT NULL
            $table->string('slug', 255)->unique(); // UNIQUE
            $table->unsignedBigInteger('parent_id')->nullable(); // FK đến chính nó
            $table->tinyInteger('status')->default(0); // 0: bản nháp, 1: hiển thị, 2: lưu trữ
            $table->timestamps();

            // Thiết lập khóa ngoại đến chính bảng categories
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
