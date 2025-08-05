<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderCanceledDueToTimeout;
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
use App\Models\Voucher;
use App\Models\VoucherUser;
use Carbon\Carbon;

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

            // Xử lý voucher
            $voucherData = null;
            $discountAmount = 0;

            if ($request->voucher_code) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->voucher_code,
                    $user,
                    $request->subtotal
                );

                if (!$voucherResponse['success']) {
                    return response()->json(['message' => $voucherResponse['message']], 400);
                }

                $voucherData = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
            }

               $shipping = 20000;
            $tax = $subtotal * 0.1;
            // $total = $subtotal + $shipping + $tax;
            $total = ($subtotal + $shipping + $tax) - $discountAmount;

            $order = $user->orders()->create([
                'order_number' => 'ORDER' . now()->format('Ymd') . '-' . rand(1000, 9999),
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                 'voucher_code' => $request->voucher_code,
                'voucher_discount' => $discountAmount,
                'voucher_type' => $voucherData->type ?? null,
                'voucher_id' => $voucherData->id ?? null,
                'discount_amount' => $discountAmount,
                // 'total' => $request->$total - $discountAmount,
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

        // Log::info('📦 Request gửi tới MoMo:', $requestData); // Add log để dễ debug

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

    // ✅ Load quan hệ để gửi về React
    $order = Order::with([
        'items.productVariant.product',
        'items.productVariant.color',
        'items.productVariant.size'
    ])->find($orderId);

    if (!$order) {
        return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
    }

    // ✅ Cập nhật nếu cần
    if ((int)$resultCode === 0 && $order->payment_status === 'pending') {
        $order->update([
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    // ✅ Trả về đầy đủ thông tin đơn hàng và sản phẩm
    return response()->json([
        'message' => (int)$resultCode === 0 ? 'Thanh toán thành công' : 'Thanh toán thất bại hoặc đã hủy',
        'data' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'items' => $order->items // => sẽ có đầy đủ product, size, color
        ]
    ], (int)$resultCode === 0 ? 200 : 400);
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
        // Giới hạn thời gian thanh toán lại là 20 phút
        $timeoutMinutes = 20;

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

        if ($order->created_at->diffInMinutes(now()) > $timeoutMinutes && $order->payment_status === 'pending' && $order->payment_method === 'momo') {
            DB::beginTransaction();
            try {
                // Xóa các item liên quan (nếu quan hệ items() có)
                if (method_exists($order, 'items')) {
                    $order->items()->delete();
                }

                // Xóa đơn hàng
                $order->delete();

                // Gửi email sau khi xóa
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->queue(new OrderCanceledDueToTimeout($order));
                    Log:: info('Gửi email đơn hàng bị hủy do hết thời gian thanh toán', ['body' => $order->toArray()]);
                    Log::error(' ');
                }

                DB::commit();
                return response()->json([
                    'message' => 'Đơn hàng đã quá thời gian thanh toán lại (20 phút) và đã bị hủy.'
                ], 410); // 410 Gone
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Lỗi khi xóa đơn hàng MoMo quá hạn: ' . $e->getMessage());
                return response()->json(['message' => 'Không thể hủy đơn hàng. Vui lòng thử lại sau.'], 500);
            }
        }

        try {
            // Gọi lại hàm tạo link thanh toán MoMo
            $paymentUrl = $this->initiateMomoPayment($order, $order->total);

            return response()->json([
                'message' => 'Tạo lại liên kết thanh toán MoMo thành công',
                'data' => [
                    'payment_url' => $paymentUrl,
                    'order_id' => $order->id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo lại thanh toán MoMo: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tạo lại link thanh toán MoMo'], 500);
        }
    }
        protected function validateAndApplyVoucher($voucherCode, $user, $subtotal)
    {
        try {
            $voucher = Voucher::where('code', $voucherCode)->first();

            if (!$voucher) {
                return ['success' => false, 'message' => 'Voucher không tồn tại'];
            }

            // Kiểm tra thời gian hiệu lực
            $now = now();
            if ($voucher->start_date && $now->lt($voucher->start_date)) {
                return ['success' => false, 'message' => 'Voucher chưa có hiệu lực'];
            }

            if ($voucher->end_date && $now->gt($voucher->end_date)) {
                return ['success' => false, 'message' => 'Voucher đã hết hạn'];
            }

            // Kiểm tra số lượng
            if ($voucher->quantity !== null && $voucher->quantity <= 0) {
                return ['success' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
            }

            // Kiểm tra giới hạn sử dụng
            if ($voucher->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucher->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucher->usage_limit) {
                    return ['success' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher này'];
                }
            }

            // Tính toán giá trị giảm giá
            $discountAmount = $this->calculateVoucherDiscount($voucher, $subtotal);

            return [
                'success' => true,
                'voucher' => $voucher,
                'discount_amount' => $discountAmount
            ];
        } catch (\Exception $e) {
            Log::error('Voucher validation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi khi kiểm tra voucher'];
        }
    }

    /**
     * Tính toán giá trị giảm giá từ voucher
     */
    protected function calculateVoucherDiscount($voucher, $subtotal)
    {
        if ($voucher->discount_type === 'amount') {
            return min($voucher->discount_amount, $subtotal);
        } elseif ($voucher->discount_type === 'percent') {
            $discount = $subtotal * ($voucher->discount_percent / 100);
            return isset($voucher->max_discount) ? min($discount, $voucher->max_discount) : $discount;
        }
        return 0;
    }


    /**
     * Cập nhật số lần sử dụng voucher
     */
    protected function updateVoucherUsage($voucher, $user)
    {
        DB::transaction(function () use ($voucher, $user) {
            // Giảm số lượng voucher
            if ($voucher->quantity !== null) {
                $voucher->decrement('quantity');
            }

            // Cập nhật số lần sử dụng của user
            $voucherUser = VoucherUser::firstOrNew([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id
            ]);

            $voucherUser->used = ($voucherUser->used ?? 0) + 1;
            $voucherUser->save();
        });
    }
}