<?php
// app/Services/Payment/MomoPaymentService.php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Mail\OrderPlaced;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MomoPaymentService
{
    public function process(Order $order, $amount)
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

        $momoApiUrl = 'https://test-payment.momo.vn/v2/gateway/api/create';
        $response = Http::timeout(30)->post($momoApiUrl, $requestData);

        if (!$response->successful()) {
            throw new \Exception('Lỗi kết nối MOMO API: ' . $response->body());
        }

        $responseData = $response->json();

        if (!isset($responseData['payUrl'])) {
            throw new \Exception($responseData['message'] ?? 'Khởi tạo thanh toán MOMO không thành công');
        }

        return [
            'success' => true,
            'payment_url' => $responseData['payUrl'],
            'order_id' => $order->id
        ];
    }

    public function handleWebhook(array $data)
    {
        $secretKey = env('MOMO_SECRET_KEY');
        $accessKey = env('MOMO_ACCESS_KEY');

        $requiredFields = [
            'amount', 'message', 'orderId', 'orderInfo',
            'orderType', 'partnerCode', 'payType', 'requestId',
            'responseTime', 'resultCode', 'transId', 'signature'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::error("Thiếu {$field} trong dữ liệu MoMo", ['data' => $data]);
                return ['success' => false, 'message' => "Thiếu trường {$field}"];
            }
        }

        $extraData = $data['extraData'] ?? '';
        $rawHash = "accessKey={$accessKey}"
            . "&amount={$data['amount']}"
            . "&extraData={$extraData}"
            . "&message={$data['message']}"
            . "&orderId={$data['orderId']}"
            . "&orderInfo={$data['orderInfo']}"
            . "&orderType={$data['orderType']}"
            . "&partnerCode={$data['partnerCode']}"
            . "&payType={$data['payType']}"
            . "&requestId={$data['requestId']}"
            . "&responseTime={$data['responseTime']}"
            . "&resultCode={$data['resultCode']}"
            . "&transId={$data['transId']}";

        $calculatedSignature = hash_hmac('sha256', $rawHash, $secretKey);

        if ($calculatedSignature !== $data['signature']) {
            Log::error('Xác minh chữ ký MoMo không thành công', [
                'calculated' => $calculatedSignature,
                'received' => $data['signature'],
                'rawHash' => $rawHash,
                'data' => $data
            ]);
            return ['success' => false, 'message' => 'Chữ ký không hợp lệ'];
        }

        $orderParts = explode('-', $data['orderId']);
        $orderId = $orderParts[0] ?? null;
        $order = Order::with(['items.productVariant.stock'])->find($orderId);

        if (!$order) {
            Log::error('Không tìm thấy đơn hàng', ['order_id' => $orderId]);
            return ['success' => false, 'message' => 'Không tìm thấy đơn hàng'];
        }

        DB::beginTransaction();
        try {
            if ((int)$data['resultCode'] === 0) {
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                    'transaction_id' => $data['transId'],
                ]);

                foreach ($order->items as $item) {
                    $item->productVariant->stock()->decrement('quantity', $item->quantity);
                }

                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    foreach ($order->items as $item) {
                        CartItem::where('cart_id', $cart->id)
                            ->where('product_variant_id', $item->product_variant_id)
                            ->where('selected', true)
                            ->delete();
                    }
                }
                
                Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                DB::commit();
                return ['success' => true, 'message' => 'Không tìm Thanh toán được xử lý thành công đơn hàng'];
            } else {
                $order->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);

                DB::commit();
                return ['success' => false, 'message' => 'Thanh toán không thành công'];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xử lý webhook MoMo: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => 'Lỗi xử lý webhook'];
        }
    }
}