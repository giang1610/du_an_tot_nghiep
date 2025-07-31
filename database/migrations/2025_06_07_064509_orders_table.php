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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('pending');
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->unsignedTinyInteger('vnp_retry_count')->default(0); // lưu số lần tạo lại thanh toán vnpay
            $table->unsignedTinyInteger('momo_retry_count')->default(0); // lưu số lần tạo lại thanh toán momo
            $table->text('shipping_address');
            $table->text('billing_address')->nullable();
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('voucher_code')->nullable(); // Mã voucher
            $table->decimal('voucher_discount', 10, 2)->default(0);
            $table->string('voucher_type')->nullable(); // 'percent' hoặc 'amount'
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('return_requested_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->string('return_media')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('return_status', ['pending', 'accepted', 'rejected'])->nullable();
            $table->text('note_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
