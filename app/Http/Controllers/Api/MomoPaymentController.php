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
use App\Models\Stock;

// realTime
use App\Events\newOder;
use App\Events\NewOrderCreated;

class MomoPaymentController extends Controller
{
    public function processMomoPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // validate request, nhận 2 voucher riêng biệt
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.product_variant_id' => 'required_with:items|integer',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'subtotal' => 'nullable|numeric',
                'product_voucher_code' => 'nullable|string',
                'shipping_voucher_code' => 'nullable|string',
            ]);
              if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $variantId = $item['product_variant_id'] ?? null;
                    $reqQty    = $item['quantity'] ?? 0;

                    if (!$variantId || $reqQty <= 0) {
                        continue; // Bỏ qua nếu dữ liệu không hợp lệ
                    }

                    // Lấy tồn kho thực tế từ DB
                    $stock = Stock::where('product_variant_id', $variantId)->first();

                    if (!$stock) {
                        return response()->json([
                            'message' => " sảm phẩm không tồn tại trong kho hoặc đã xóa vui lòng mua sản phẩm khác."
                        ], 400);
                    }

                    if ($reqQty > $stock->quantity) {
                        return response()->json([
                            'message' => "Sản phẩm trong kho không đủ số lượng."
                        ], 400);
                    }
                }
            }

            // xử lý items (buy-now) hoặc lấy từ cart
            if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                $itemsFromRequest = collect($request->items)->map(function ($it) {
                    return (object) [
                        'product_variant_id' => isset($it['product_variant_id']) ? (int)$it['product_variant_id'] : (isset($it['variant_id']) ? (int)$it['variant_id'] : null),
                        'quantity' => isset($it['quantity']) ? (int)$it['quantity'] : 1,
                    ];
                })->filter(function ($it) {
                    return !empty($it->product_variant_id);
                })->values();

                if ($itemsFromRequest->isEmpty()) {
                    return response()->json(['message' => 'Không có sản phẩm hợp lệ trong payload.'], 400);
                }

                $variantIds = $itemsFromRequest->pluck('product_variant_id')->toArray();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

                $subtotal = 0;
                foreach ($itemsFromRequest as $it) {
                    $variant = $variants->get($it->product_variant_id);
                    if (!$variant) {
                        return response()->json(['message' => "Biến thể (id={$it->product_variant_id}) không tồn tại."], 400);
                    }
                    $price = $variant->sale_price ?? $variant->price;
                    $subtotal += $price * $it->quantity;
                }

                $cartItems = $itemsFromRequest;
                $isBuyNow = true;
            } else {
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

                $cartItems = $cart->items;
                $isBuyNow = false;
            }

            // --- Voucher xử lý: product + shipping ---
            $productVoucher = null;
            $shippingVoucher = null;
            $productDiscount = 0;
            $shippingDiscount = 0;

            // product voucher (áp dụng trên subtotal)
            if ($request->product_voucher_code) {
                $vr = $this->validateAndApplyVoucher($request->product_voucher_code,$request->shipping_voucher_code, $user, $subtotal, 'product');
                if (!$vr['success']) {
                    return response()->json(['message' => $vr['message']], 400);
                }
                $productVoucher = $vr['voucher'];
                $productDiscount = $vr['discount_amount'] ?? 0;
              
            }

            // phí shipping cơ bản
            $shipping = 20000;

            // shipping voucher (áp dụng trên shipping)
            if ($request->shipping_voucher_code) {
                $svr = $this->validateAndApplyVoucher($request->shipping_voucher_code, $user, $shipping, 'shipping');
                if (!$svr['success']) {
                    return response()->json(['message' => $svr['message']], 400);
                }
                $shippingVoucher = $svr['voucher'];
                $shippingDiscount = $svr['discount_amount_shipping'] ?? 0;
                // áp dụng giảm vào phí vận chuyển
                $shipping = max(0, $shipping - $shippingDiscount);
            }

            $tax = $subtotal * 0.1;
            $totalBeforeDiscounts = $subtotal + $shipping + $tax;
            $total = $totalBeforeDiscounts - ($productDiscount );
            $total = max(0, $total);

            // Tạo Order -> lưu cả 2 voucher
            $order = $user->orders()->create([
                'order_number' => 'ORDER' . now()->format('Ymd') . '-' . rand(1000, 9999),
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                // product voucher fields (cần migration nếu chưa có)
                'product_voucher_code' => $productVoucher->code ?? null,
                'product_voucher_id' => $productVoucher->id ?? null,
                'product_voucher_discount' => $productDiscount,
                // shipping voucher fields
                'shipping_voucher_code' => $shippingVoucher->code ?? null,
                'shipping_voucher_id' => $shippingVoucher->id ?? null,
                'shipping_voucher_discount' => $shippingDiscount,
                // tổng discount
                'voucher_discount' => ($productDiscount + $shippingDiscount),
                'discount_amount' => ($productDiscount + $shippingDiscount),
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

            // tạo OrderItem
            $variantIdsForOrder = [];
            foreach ($cartItems as $ci) {
                $variantIdsForOrder[] = $ci->product_variant_id ?? $ci->product_variant_id ?? null;
            }
            $variantIdsForOrder = array_filter($variantIdsForOrder);
            $variantsMap = \App\Models\ProductVariant::whereIn('id', $variantIdsForOrder)->get()->keyBy('id');

            foreach ($cartItems as $item) {
                $productVariantId = $item->product_variant_id ?? ($item->product_variant_id ?? null);
                if (!$productVariantId) continue;

                $variant = $variantsMap->get($productVariantId);
                if (!$variant) {
                    throw new \Exception("Biến thể (id={$productVariantId}) không tồn tại khi tạo đơn.");
                }

                $quantity = $item->quantity ?? 1;
                $price = $variant->price;
                $salePrice = $variant->sale_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'color_id' => $variant->color_id ?? null,
                    'size_id' => $variant->size_id ?? null,
                ]);
            }

            // Gọi MoMo
            $momoResponse = $this->initiateMomoPayment($order, $total);

            DB::commit();

            return response()->json([
                'message' => 'Đã khởi tạo thanh toán MOMO',
                'data' => [
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                    'payment_url' => $momoResponse['payUrl'] ?? ($momoResponse['pay_url'] ?? null),
                    'order_id' => $order->id,
                    'is_buy_now' => $isBuyNow,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo MOMO: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

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
                broadcast(new  newOder($order));
                event(new NewOrderCreated($order->order_number, $order->id));
                


                // Giảm tồn kho an toàn
                foreach ($order->items as $item) {
                    if (isset($item->productVariant) && method_exists($item->productVariant, 'stock')) {
                        $item->productVariant->stock()->decrement('quantity', $item->quantity);
                    } elseif (isset($item->variant) && method_exists($item->variant, 'stock')) {
                        // fallback nếu relation khác tên
                        $item->variant->stock()->decrement('quantity', $item->quantity);
                    }
                }

                // Xóa các item selected trong cart (nếu có)
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    foreach ($order->items as $item) {
                        CartItem::where('cart_id', $cart->id)
                            ->where('product_variant_id', $item->product_variant_id)
                            ->where('selected', true)
                            ->delete();
                    }
                }

                // Cập nhật usage cho voucher (khi payment thành công)
                if ($order->product_voucher_id) {
                    $voucher = Voucher::find($order->product_voucher_id);
                    if ($voucher) $this->updateVoucherUsage($voucher, $order->user);
                }
                if ($order->shipping_voucher_id) {
                    $voucher = Voucher::find($order->shipping_voucher_id);
                    if ($voucher) $this->updateVoucherUsage($voucher, $order->user);
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

        $order = Order::with([
            'items.productVariant.product',
            'items.productVariant.color',
            'items.productVariant.size'
        ])->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ((int)$resultCode === 0 && $order->payment_status === 'pending') {
            $order->update([
                'status' => 'pending',
                'payment_status' => 'paid',
            ]);

            // update voucher usage nếu chưa cập nhật
            if ($order->product_voucher_id) {
                $voucher = Voucher::find($order->product_voucher_id);
                if ($voucher) $this->updateVoucherUsage($voucher, $order->user);
            }
            if ($order->shipping_voucher_id) {
                $voucher = Voucher::find($order->shipping_voucher_id);
                if ($voucher) $this->updateVoucherUsage($voucher, $order->user);
            }

            // decrement stock an toàn
            foreach ($order->items as $item) {
                if (isset($item->productVariant) && method_exists($item->productVariant, 'stock')) {
                    $item->productVariant->stock()->decrement('quantity', $item->quantity);
                } elseif (isset($item->variant) && method_exists($item->variant, 'stock')) {
                    $item->variant->stock()->decrement('quantity', $item->quantity);
                }
            }
        }

        return response()->json([
        'message' => (int)$resultCode === 0 ? 'Thanh toán thành công' : 'Thanh toán thất bại hoặc đã hủy',
        'data' => [

            'total' => $order->total,
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

        if ($order->created_at->diffInMinutes(now()) > $timeoutMinutes && $order->payment_status === 'pending') {
            DB::beginTransaction();
            try {
                if (method_exists($order, 'items')) {
                    $order->items()->delete();
                }

                $order->delete();

                if ($order->customer_email) {
                    Mail::to($order->customer_email)->queue(new OrderCanceledDueToTimeout($order));
                    Log::info('Gửi email đơn hàng bị hủy do hết thời gian thanh toán', ['body' => $order->toArray()]);
                }

                DB::commit();
                return response()->json([
                    'message' => 'Đơn hàng đã quá thời gian thanh toán lại (20 phút) và đã bị hủy.'
                ], 410);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Lỗi khi xóa đơn hàng MoMo quá hạn: ' . $e->getMessage());
                return response()->json(['message' => 'Không thể hủy đơn hàng. Vui lòng thử lại sau.'], 500);
            }
        }

        try {
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

    /**
     * validateAndApplyVoucher: Bây giờ hỗ trợ $amountContext và $appliesTo = 'product'|'shipping'
     */
protected function validateAndApplyVoucher($voucherCode, $voucher2, $user, $amountContext, $appliesTo = 'product')
{
    try {
        $voucher = Voucher::where('code', $voucherCode)->first();
        $voucherShipping = Voucher::where('code', $voucher2)->first();

        // Nếu cả 2 đều không tồn tại
        if (!$voucher && !$voucherShipping) {
            return ['success' => false, 'message' => 'Voucher không tồn tại'];
        }

        $now = now();

        // ==== Xử lý voucher sản phẩm ====
        $discountAmount = 0;
        if ($voucher) {
            if (isset($voucher->applies_to) && $voucher->applies_to !== 'all' && $voucher->applies_to !== $appliesTo) {
                return ['success' => false, 'message' => 'Voucher không áp dụng cho mục này'];
            }

            if ($voucher->start_date && $now->lt($voucher->start_date)) {
                return ['success' => false, 'message' => 'Voucher chưa có hiệu lực'];
            }

            if ($voucher->end_date && $now->gt($voucher->end_date)) {
                return ['success' => false, 'message' => 'Voucher đã hết hạn'];
            }

            if ($voucher->quantity !== null && $voucher->quantity <= 0) {
                return ['success' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
            }
            $userId = is_object($user) ? $user->id : $user;


            if ($voucher->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucher->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucher->usage_limit) {
                    return ['success' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher này'];
                }
            }

            if ($voucher->discount_type === 'amount') {
                $discountAmount = min($voucher->discount_amount, $amountContext);
            } elseif ($voucher->discount_type === 'percent') {
                $discount = $amountContext * ($voucher->discount_percent / 100);
                $discountAmount = isset($voucher->max_discount) ? min($discount, $voucher->max_discount) : $discount;
            }
        }

        // ==== Xử lý voucher shipping ====
        $discountAmountShipping = 0;
        if ($voucherShipping) {
            if (isset($voucherShipping->applies_to) && $voucherShipping->applies_to !== 'all' && $voucherShipping->applies_to !== 'shipping') {
                return ['success' => false, 'message' => 'Voucher không áp dụng cho phí vận chuyển'];
            }

            if ($voucherShipping->start_date && $now->lt($voucherShipping->start_date)) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển chưa có hiệu lực'];
            }

            if ($voucherShipping->end_date && $now->gt($voucherShipping->end_date)) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển đã hết hạn'];
            }

            if ($voucherShipping->quantity !== null && $voucherShipping->quantity <= 0) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển đã hết lượt sử dụng'];
            }

            if ($voucherShipping->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucherShipping->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucherShipping->usage_limit) {
                    return ['success' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher phí vận chuyển'];
                }
            }

            if ($voucherShipping->discount_type === 'amount') {
                $discountAmountShipping = min($voucherShipping->discount_amount, $amountContext);
            } elseif ($voucherShipping->discount_type === 'percent') {
                $discount = $amountContext * ($voucherShipping->discount_percent / 100);
                $discountAmountShipping = isset($voucherShipping->max_discount) ? min($discount, $voucherShipping->max_discount) : $discount;
            }
        }

        return [
            'success' => true,
            'voucher' => $voucher,
            'voucher_shipping' => $voucherShipping,
            'discount_amount' => $discountAmount,
            'discount_amount_shipping' => $discountAmountShipping
        ];

    } catch (\Exception $e) {
        Log::error('Voucher validation error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi khi kiểm tra voucher'];
    }
}



    /**
     * Cập nhật số lần sử dụng voucher
     */
    protected function updateVoucherUsage($voucher, $user)
    {
        DB::transaction(function () use ($voucher, $user) {
            if ($voucher->quantity !== null) {
                $voucher->decrement('quantity');
            }

            $voucherUser = VoucherUser::firstOrNew([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id
            ]);

            $voucherUser->used = ($voucherUser->used ?? 0) + 1;
            $voucherUser->save();
        });
    }
}