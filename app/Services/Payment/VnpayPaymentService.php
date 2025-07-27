<?php

namespace App\Services\Payment;

use App\Models\Order;
use Carbon\Carbon;

class VnpayPaymentService
{
    public function process(Order $order)
    {
        $paymentUrl = $this->initiatePayment($order);
        
        return [
            'message' => 'Đã khởi tạo thanh toán VNPay',
            'data' => [
                'order_id' => $order->id,
                'payment_url' => $paymentUrl,
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
            ]
        ];
    }

    protected function initiatePayment($order)
    {
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_ReturnUrl = env('VNP_RETURN_URL');

        $vnp_TxnRef = $order->id . '_' . time();
        $vnp_OrderInfo = 'Thanh toan hoa don ' . $order->order_number;
        $vnp_OrderType = 'other';
        $vnp_Amount = $order->total * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'VNBANK';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => Carbon::now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);

        $hashdata = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        return $vnp_Url . "?" . $hashdata . "&vnp_SecureHash=" . $vnpSecureHash;
    }
}