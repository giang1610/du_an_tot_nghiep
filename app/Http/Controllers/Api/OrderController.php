<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Mail\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Voucher;
use App\Models\VoucherUser;

// RealTime
use App\Events\ProductStockUpdated;
use App\Events\NewOrderCreated;

class OrderController extends Controller
{
    protected $orderValidationRules = [
        'shipping_address' => 'required|string|max:255',
        'billing_address' => 'nullable|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    /**
     * Tạo đơn hàng mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,processing,completed,cancelled,failed',
            'payment_method' => 'nullable|string|in:cod,momo',
            'payment_status' => 'nullable|string|in:pending,paid,unpaid,failed',
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'shipping' => $request->shipping ?? 0,
                'total' => $request->total,
                'status' => $request->status ?? 'pending',
                'payment_method' => $request->payment_method ?? 'cod',
                'payment_status' => $request->payment_status ?? ($request->payment_method === 'cod' ? 'unpaid' : 'pending'),
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes ?? null,
            ]);

            // Tạo order items
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                ]);

                // Trừ tồn kho
                $variant->stock()->decrement('quantity', $item['quantity']);
                event(new ProductStockUpdated($variant->id, $variant->fresh()->stock->quantity));
            }

            // Gửi email xác nhận
            Mail::to($request->customer_email)->queue(new OrderPlaced($order, $order->items()->with(['productVariant.product', 'productVariant.color', 'productVariant.size'])->get()));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công',
                'data' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');

        $query = Auth::user()->orders()
            ->with(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => $orders
        ]);
    }

    /**
     * Xem chi tiết đơn hàng
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ], 403);
        }

        $order->load(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin đơn hàng thành công',
            'data' => $order
        ]);
    }

    /**
     * Cập nhật địa chỉ đơn hàng
     */
    public function updateAddress(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->shipping_address = $request->shipping_address;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $order,
        ]);
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Hoàn lại tồn kho nếu đã trừ
            if ($order->payment_status === 'paid' || $order->payment_method === 'cod') {
                foreach ($order->items as $item) {
                    $variant = $item->productVariant;
                    $variant->stock()->increment('quantity', $item->quantity);
                    event(new ProductStockUpdated($variant->id, $variant->fresh()->stock->quantity));
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'cancelled',
                'payment_status' => ($order->payment_status === 'paid') ? 'refunded' : 'cancelled',
            ]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi hủy đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý thanh toán (COD hoặc MOMO)
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();

        // Lấy giỏ hàng
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giỏ hàng.'
            ], 404);
        }

        // Lấy các sản phẩm được chọn
        $cartItems = CartItem::with('productVariant')
            ->where('cart_id', $cart->id)
            ->where('selected', true)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm nào được chọn để thanh toán.'
            ], 400);
        }

        // Tạo mảng items cho đơn hàng
        $items = $cartItems->map(function ($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $request->merge(['items' => $items]);

        // Validate
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cod,vnpay,momo',
            'shipping_address' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'voucher_code' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || !$variant->stock || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
            }

            $voucher = null;
            $discountAmount = 0;

            // Xử lý voucher nếu có
            if ($request->filled('voucher_code')) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->voucher_code, 
                    $user, 
                    $request->subtotal
                );

                if (!$voucherResponse['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $voucherResponse['message']
                    ], 400);
                }

                $voucher = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
                
                // Cập nhật voucher usage
                $this->updateVoucherUsage($voucher, $user);
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'shipping' => $request->shipping,
                'total' => $request->total - $discountAmount,
                'discount_amount' => $discountAmount,
                'voucher_id' => $voucher ? $voucher->id : null,
                'voucher_code' => $voucher ? $voucher->code : null,
                'status' => 'pending',
            ]);

            // Tạo order items
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $variant->sale_price ?? $variant->price,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                ]);

                // Trừ kho ngay nếu là COD
                if ($request->payment_method === 'cod') {
                    $variant->stock->decrement('quantity', $item['quantity']);
                    event(new ProductStockUpdated($variant->id, $variant->fresh()->stock->quantity));
                }
            }

            event(new NewOrderCreated($order->order_number, $order->id));

            DB::commit();

            // Xử lý theo phương thức thanh toán
            switch ($request->payment_method) {
                case 'momo':
                    $momoResponse = $this->initiateMomoPayment($order, $order->total);
                    return response()->json([
                        'success' => true,
                        'message' => 'Đã khởi tạo thanh toán MOMO',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $momoResponse['payUrl'],
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);

                case 'cod':
                    Mail::to($request->customer_email)->queue(new OrderPlaced($order, $user));
                    return response()->json([
                        'success' => true,
                        'message' => 'Đặt hàng COD thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);

                default:
                    return response()->json([
                        'success' => true,
                        'message' => 'Đặt hàng thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác nhận đã nhận hàng
     */
    public function confirmReceived($orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($order->status !== 'shipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xác nhận đơn hàng này'
                ], 400);
            }

            $order->status = 'delivered';
            $order->delivered_at = now();

            if ($order->payment_method === 'cod') {
                $order->payment_status = 'paid';
            }

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã xác nhận nhận hàng thành công'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Confirm received error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xác nhận nhận hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra và áp dụng voucher
     */
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
        } else {
            $discount = $subtotal * ($voucher->discount_percent / 100);
            return isset($voucher->max_discount) ? min($discount, $voucher->max_discount) : $discount;
        }
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

    /**
     * Khởi tạo thanh toán MOMO
     */
    protected function initiateMomoPayment($order, $amount)
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
            throw new \Exception($responseData['message'] ?? 'Khởi tạo thanh toán MOMO thất bại');
        }

        return $responseData;
    }

    /**
     * Xử lý webhook thông báo từ MOMO
     */
    public function momoWebhook(Request $request)
    {
        $data = $request->all();

        $secretKey = env('MOMO_SECRET_KEY');
        $accessKey = env('MOMO_ACCESS_KEY');

        // Kiểm tra các trường bắt buộc
        $requiredFields = [
            'amount', 'message', 'orderId', 'orderInfo',
            'orderType', 'partnerCode', 'payType', 'requestId',
            'responseTime', 'resultCode', 'transId', 'signature'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::error("Missing field {$field} in MoMo data", ['data' => $data]);
                return response()->json(['success' => false, 'message' => "Thiếu trường {$field}"], 400);
            }
        }

        // Xác minh chữ ký
        $extraData = $data['extraData'] ?? '';
        $rawHash = "accessKey={$accessKey}&amount={$data['amount']}&extraData={$extraData}&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&transId={$data['transId']}";

        $calculatedSignature = hash_hmac('sha256', $rawHash, $secretKey);

        if ($calculatedSignature !== $data['signature']) {
            Log::error('MoMo signature verification failed', [
                'calculated' => $calculatedSignature,
                'received' => $data['signature'],
                'rawHash' => $rawHash
            ]);
            return response()->json(['success' => false, 'message' => 'Chữ ký không hợp lệ'], 403);
        }

        // Xử lý đơn hàng
        $orderParts = explode('-', $data['orderId']);
        $orderId = $orderParts[0] ?? null;

        if (!$orderId) {
            Log::error('Cannot extract orderId from string', ['orderId_raw' => $data['orderId']]);
            return response()->json(['success' => false, 'message' => 'orderId không hợp lệ'], 400);
        }

        $order = Order::with(['items.productVariant.stock'])->find($orderId);

        if (!$order) {
            Log::error('Order not found', ['order_id' => $orderId]);
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        DB::beginTransaction();

        try {
            if ((int)$data['resultCode'] === 0) {
                // Thanh toán thành công
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                    'transaction_id' => $data['transId'],
                ]);

                // Trừ tồn kho
                foreach ($order->items as $item) {
                    $item->variant->stock()->decrement('quantity', $item->quantity);
                    event(new ProductStockUpdated($item->variant->id, $item->variant->fresh()->stock->quantity));
                }

                // Xóa sản phẩm đã mua khỏi giỏ hàng
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)
                        ->whereIn('product_variant_id', $order->items->pluck('product_variant_id'))
                        ->where('selected', true)
                        ->delete();
                }

                // Gửi email xác nhận
                Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Xử lý thanh toán thành công']);
            } else {
                // Thanh toán thất bại
                $order->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);

                DB::commit();
                return response()->json(['success' => false, 'message' => 'Thanh toán thất bại'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MoMo webhook processing error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý webhook'], 500);
        }
    }

    /**
     * Xử lý URL trả về từ MOMO
     */
    public function momoReturn(Request $request)
    {
        $orderId = $request->query('orderId');
        $resultCode = $request->query('resultCode');

        if (is_null($orderId) || is_null($resultCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Tham số không hợp lệ'
            ], 400);
        }

        // Trích xuất ID đơn hàng
        $orderParts = explode('-', $orderId);
        $orderId = $orderParts[0];
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        if ((int)$resultCode === 0) {
            return response()->json([
                'success' => true,
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
            'success' => false,
            'message' => 'Thanh toán thất bại hoặc đã hủy',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ]
        ], 400);
    }
}
