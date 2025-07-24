<?php
// app/Services/Payment/VnpayPaymentService.php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Mail\OrderPlaced;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VnpayPaymentService
{
    public function process(Order $order, $amount)
    {
        try {
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

            $queryString = "";
            $hashdata = "";
            $i = 0;
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $queryString .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $queryString = rtrim($queryString, '&');
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= "?" . $queryString . "&vnp_SecureHash=" . $vnpSecureHash;

            return [
                'success' => true,
                'payment_url' => $vnp_Url,
            ];

        } catch (\Exception $e) {
            Log::error('VNPay payment URL generation error: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function handleWebhook(array $inputData)
    {
        try {
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);

            ksort($inputData);

            $hashData = '';
            $i = 0;
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            if ($secureHash !== $vnp_SecureHash) {
                return ['success' => false, 'message' => 'Invalid checksum'];
            }

            $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            $orderId = $orderParts[0] ?? null;

            if (!$orderId || !is_numeric($orderId)) {
                return ['success' => false, 'message' => 'Invalid order ID'];
            }

            $order = Order::with(['items.productVariant.stock'])->find($orderId);
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            if ($inputData['vnp_ResponseCode'] === '00') {
                DB::beginTransaction();
                try {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'transaction_id' => $inputData['vnp_TransactionNo'],
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
                    return ['success' => true, 'message' => 'Payment successful'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('VNPay IPN processing error: ' . $e->getMessage(), ['exception' => $e]);
                    return ['success' => false, 'message' => 'Payment processing error'];
                }
            }

            return ['success' => false, 'message' => 'Payment failed'];
        } catch (\Exception $e) {
            Log::error('VNPay IPN system error: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => 'System error'];
        }
    }
}