<?php
// app/Services/Payment/CodPaymentService.php

namespace App\Services\Payment;

use App\Models\Order;
use App\Mail\OrderPlaced;
use Illuminate\Support\Facades\Mail;

class CodPaymentService
{
    public function process(Order $order, $amount)
    {
        // Xử lý thanh toán COD
        $order->update([
            'payment_status' => 'unpaid',
            'status' => 'processing'
        ]);

        // Gửi email xác nhận
        Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

        return [
            'success' => true,
            'payment_url' => null,
            'message' => 'Thanh toán COD đã được ghi nhận. Vui lòng chuẩn bị tiền mặt khi nhận hàng.'
        ];
    }

    // COD không cần xử lý webhook
    public function handleWebhook(array $data)
    {
        return [
            'success' => false,
            'message' => 'Không có webhook cho phương thức thanh toán COD'
        ];
    }
}