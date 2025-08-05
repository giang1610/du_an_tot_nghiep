<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // users
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // orders
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade'); // product_variants
            $table->string('media')->nullable(); // Đường dẫn file ảnh/video đánh giá
            $table->tinyInteger('review_round'); // 1 hoặc 2
            $table->tinyInteger('rating')->checkBetween(1, 5); // Laravel 10+ hỗ trợ checkBetween
            $table->text('content')->nullable();
            $table->boolean('status')->default(true); // Trạng thái đánh giá: true - hiển thị, false - ẩn đánh giá
            $table->timestamps();

            $table->unique(['user_id', 'order_id', 'product_variant_id', 'review_round']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};