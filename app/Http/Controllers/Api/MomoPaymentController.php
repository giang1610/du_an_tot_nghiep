<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderPlaced;

class MomoPaymentController extends Controller
{
    public function processMomoPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $cart = Cart::with(['items' => function ($q) {
                $q->where('selected', true);
            }, 'items.variant'])->where('user_id', $user->id)->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
            }

            $subtotal = 0;
            foreach ($cart->items as $item) {
                if (!$item->variant) {
                    throw new \Exception("Sản phẩm không tồn tại hoặc bị lỗi biến thể.");
                }
                $subtotal += ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
            }

            $shipping = 20000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;

            $order = $user->orders()->create([
                'order_number' => 'ORDER' . now()->format('Ymd') . '-' . rand(1000, 9999),
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => 'momo',
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'sale_price' => $item->variant->sale_price,
                    'color_id' => $item->variant->color_id,
                    'size_id' => $item->variant->size_id,
                ]);
            }

            $momoResponse = $this->initiateMomoPayment($order, $total);

            DB::commit();

            return response()->json([
                'message' => 'Đã khởi tạo thanh toán MOMO',
                'data' => [
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                    'payment_url' => $momoResponse['payUrl'],
                    'order_id' => $order->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo MOMO: ' . $e->getMessage());

            return response()->json([
                'message' => 'Lỗi khởi tạo thanh toán MOMO',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function initiateMomoPayment($order, $amount)
    {
        $endpoint     = env('MOMO_API_URL');
        $partnerCode  = env('MOMO_PARTNER_CODE');
        $accessKey    = env('MOMO_ACCESS_KEY');
        $secretKey    = env('MOMO_SECRET_KEY');
        $redirectUrl  = env('MOMO_REDIRECT_URL');
        $ipnUrl       = env('MOMO_IPN_URL');
        $requestType  = env('MOMO_REQUEST_TYPE', 'payWithATM');

        $extraData = "";
        $requestId = (string) Str::uuid();
        $orderId = $order->id . '-' . time();
        $orderInfo = "Thanh toán đơn hàng #{$order->id}";

        $amount = (int) round($amount);
        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $requestData = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
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

        Log::info('📦 Request gửi tới MoMo:', $requestData); // Add log để dễ debug

        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post($endpoint, $requestData);

        if (!$response->successful()) {
            Log::error('❌ Momo API response lỗi:', ['body' => $response->body()]);
            throw new \Exception('Lỗi kết nối MOMO API: ' . $response->body());
        }

        $responseData = $response->json();

        if ($responseData['resultCode'] != 0 || !isset($responseData['payUrl'])) {
            throw new \Exception($responseData['message'] ?? 'Khởi tạo thanh toán MOMO thất bại');
        }

        return $responseData;
    }


    public function momoIpn(Request $request)
    {
        $data = $request->all();
        $secretKey = env('MOMO_SECRET_KEY');
        $accessKey = env('MOMO_ACCESS_KEY');

        $rawHash = "accessKey={$accessKey}"
            . "&amount={$data['amount']}"
            . "&extraData=" . ($data['extraData'] ?? '')
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
            Log::error('Sai chữ ký MoMo', ['data' => $data]);
            return response()->json(['message' => 'Chữ ký không hợp lệ'], 403);
        }

        Log::info('✅ Xác minh chữ ký MOMO thành công');

        $orderId = explode('-', $data['orderId'])[0];
        $order = Order::with(['items.productVariant.stock'])->find($orderId);

        if (!$order) {
            Log::error('Không tìm thấy đơn hàng', ['order_id' => $orderId]);
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
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
                    $item->variant->stock()->decrement('quantity', $item->quantity);
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
                return response()->json(['message' => 'Xử lý thanh toán thành công'], 200);
            } else {
                $order->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);
                DB::commit();
                return response()->json(['message' => 'Thanh toán thất bại'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi IPN MoMo: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi xử lý webhook'], 500);
        }
    }

    public function momoReturn(Request $request)
    {
        $orderId = $request->query('orderId');
        $resultCode = $request->query('resultCode');

        if (is_null($orderId) || is_null($resultCode)) {
            return response()->json(['message' => 'Tham số không hợp lệ'], 400);
        }

        $orderId = explode('-', $orderId)[0];
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ((int)$resultCode === 0) {
            return response()->json([
                'message' => 'Thanh toán thành công',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                ]
            ]);
        }

        return response()->json([
            'message' => 'Thanh toán thất bại hoặc đã hủy',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ]
        ], 400);
    }

    protected function refundMomoPayment(Order $order, $amount = null)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/refund";
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $requestId = Str::uuid();
        $amount = $amount ?? $order->total;

        $rawHash = "accessKey={$accessKey}&amount={$amount}&orderId={$order->id}&partnerCode={$partnerCode}&requestId={$requestId}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $response = Http::post($endpoint, [
            'partnerCode' => $partnerCode,
            'orderId' => $order->id,
            'requestId' => $requestId,
            'amount' => $amount,
            'transId' => $order->transaction_id,
            'signature' => $signature,
        ]);

        if ($response->successful()) {
            return ['success' => true];
        } else {
            Log::error('Refund MOMO thất bại', ['body' => $response->body()]);
            return ['success' => false, 'message' => $response->json()['message'] ?? 'Lỗi không xác định'];
        }
    }

    public function retryMomoPayment(Request $request)
    {
        $user = Auth::user();
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');

        // Số lần tối đa tạo lại link MoMo
        $maxRetry = 3;

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('payment_method', 'momo')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->payment_method !== 'momo') {
            return response()->json(['message' => 'Đơn hàng không dùng ví thanh toán Momo'], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Đơn hàng đã được thanh toán thành công'], 400);
        }

        if ($order->momo_retry_count >= $maxRetry) {
            return response()->json(['message' => 'Bạn đã vượt quá số lần thanh toán lại bằng Ví MoMo. Hãy tạo đơn hàng khác'], 429);
        }

        try {
            // Gọi lại hàm tạo link thanh toán MoMo
            $paymentUrl = $this->initiateMomoPayment($order, $order->total);

            // Cập nhật số lần retry
            $order->increment('momo_retry_count');

            return response()->json([
                'message' => 'Tạo lại liên kết thanh toán MoMo thành công',
                'data' => [
                    'payment_url' => $paymentUrl,
                    'order_id' => $order->id,
                    'retry_count' => $order->momo_retry_count + 1
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo lại thanh toán MoMo: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tạo lại link thanh toán MoMo'], 500);
        }
    }
}
