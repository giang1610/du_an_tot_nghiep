<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Mail\OrderPlaced;
use Illuminate\Support\Facades\Mail;

class CodPaymentService
{
    public function process(Order $order)
    {
        Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));
        
        return [
            'message' => 'Đặt hàng COD thành công',
            'data' => [
                'order_id' => $order->id,
                'payment_url' => null,
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
            ]
        ];
    }
}