<?php
namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class MomoPaymentService
{
    public function process(Order $order)
    {
        $response = $this->initiatePayment($order, $order->total);
        
        return [
            'message' => 'Đã khởi tạo thanh toán MOMO',
            'data' => [
                'order_id' => $order->id,
                'payment_url' => $response['payUrl'],
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
            ]
        ];
    }

    protected function initiatePayment($order, $amount)
    {
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $redirectUrl = env('MOMO_REDIRECT_URL');
        $ipnUrl = env('MOMO_IPN_URL');
        $requestType = env('MOMO_REQUEST_TYPE', 'payWithATM');

        $extraData = "";
        $requestId = (string) Str::uuid();
        $orderId = $order->id . '-' . time();
        $orderInfo = "Thanh toán đơn hàng #{$order->order_number}";

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $requestData = [
            'partnerCode' => $partnerCode,
            'partnerName' => env('APP_NAME'),
            'storeId' => 'MOMO_STORE',
            'requestId' => $requestId,
            'amount' => (string) $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $response = Http::timeout(30)->post('https://test-payment.momo.vn/v2/gateway/api/create', $requestData);

        if (!$response->successful()) {
            throw new \Exception('Lỗi kết nối MOMO API: ' . $response->body());
        }

        return $response->json();
    }
}