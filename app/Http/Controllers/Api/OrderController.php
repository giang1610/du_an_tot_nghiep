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

// RealTime
use App\Events\ProductStockUpdated;
use App\Events\NewOrderCreated;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $orderValidationRules = [
        'shipping_address' => 'required|string|max:255',
        'billing_address' => 'nullable|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,processing,completed,cancelled,failed',
            'payment_method' => 'nullable|string|in:cod,momo,vnpay',
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
                'message' => 'Lỗi xác thực',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
            }

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

                $variant->stock()->decrement('quantity', $item['quantity']);

            }

            Mail::to($request->customer_email)->queue(new OrderPlaced($order, $order->items()->with(['productVariant.product', 'productVariant.color', 'productVariant.size'])->get()));

            DB::commit();

            return response()->json([
                'message' => 'Tạo đơn hàng thành công',
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])

            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => $orders
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $order->load(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);

        return response()->json([
            'message' => 'Lấy thông tin đơn hàng thành công',
            'data' => $order
        ]);
    }

    public function updateAddress(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $request->validate([
            'shipping_address' => 'required|string|max:255',
        ]);

        $order->shipping_address = $request->input('shipping_address');
        $order->save();

        return response()->json([
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.'], 400);
        }

        // $order->status = 'cancelled';
        // $order->save();

        // return response()->json(['message' => 'Hủy đơn hàng thành công']);
        DB::beginTransaction();

        try {
            // Hoàn lại tồn kho nếu đã trừ (COD hoặc thanh toán online thành công)
            if ($order->payment_status === 'paid' || $order->payment_method === 'cod') {
                foreach ($order->items as $item) {
                    $variant = $item->productVariant;
                    $variant->stock()->increment('quantity', $item->quantity);
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'cancelled',
                'payment_status' => ($order->payment_status === 'paid') ? 'refunded' : 'cancelled',
            ]);

            DB::commit();
            return response()->json(['message' => 'Hủy đơn hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hủy đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi hủy đơn hàng'], 500);
        }
    }


    /**
     * Xử lý thanh toán (COD hoặc MOMO)
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();

         // Lấy cart của user
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Không tìm thấy giỏ hàng.'], 404);
        }

        // Lấy các sản phẩm được chọn để mua (selected = 1)
        $cartItems = CartItem::with('productVariant')
            ->where('cart_id', $cart->id)
            ->where('selected', true)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
        }

        // Tạo mảng items cho đơn hàng từ cartItems
        $items = $cartItems->map(function($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Gán lại vào $request để dùng chung validate và xử lý phía dưới
        $request->merge(['items' => $items]);

        // ...phần validate và xử lý tạo đơn hàng giữ nguyên như cũ...
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cod,vnpay,momo',
            'shipping_address' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {

            // Kiểm tra tồn kho trước
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || !$variant->stock || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
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
                'total' => $request->total,
                'status' => 'pending',
            ]);

            // Tạo các item cho đơn hàng
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

                // Trừ kho ngay nếu là COD, còn MOMO sẽ trừ khi nhận webhook
                if ($request->payment_method === 'cod') {
                    $variant->stock->decrement('quantity', $item['quantity']);
                    broadcast(new ProductStockUpdated(
                        $variant->id,
                        $variant->fresh()->stock->quantity
                    ));
                }
            }

            event(new NewOrderCreated($order->order_number, $order->id));

            event(new NewOrderCreated($order->order_number, $order->id));

            DB::commit();

            // Xử lý theo phương thức thanh toán
            switch ($request->payment_method) {
                case 'momo':
                    $momoResponse = $this->initiateMomoPayment($order, $order->total);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán MOMO',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $momoResponse['payUrl'],
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);

                case 'cod':
                    // Gửi email xác nhận cho COD
                    Mail::to($request->customer_email)->queue(new OrderPlaced($order, $user));
                    return response()->json([
                        'message' => 'Đặt hàng COD thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => null,
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);

                case 'vnpay':
                    $vnpResponse = $this->initiateVnpayPayment($order);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán VNPay',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $vnpResponse['payment_url'],
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);

                default:
                    return response()->json([
                        'message' => 'Đặt hàng thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không thể tạo đơn hàng',
                'error' => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Xử lý thanh toán COD
     */
    protected function processCodPayment($user, $request, $cart, $totals)
    {
        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho
            foreach ($cart->items as $item) {
                if ($item->variant->stock->quantity < $item->quantity) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item->variant->product->name}");
                }
            }

            // Tạo đơn hàng
            $order = $user->orders()->create([
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'status' => 'processing',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            // Tạo items đơn hàng và cập nhật tồn kho
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

                // Cập nhật tồn kho
                $item->variant->stock()->decrement('quantity', $item->quantity);
            }

            // Xóa các sản phẩm đã chọn khỏi giỏ hàng
            $cart->items()->where('selected', true)->delete();

            // Gửi email xác nhận
            Mail::to($user->email)->queue(new OrderPlaced($order, $user));

            DB::commit();

            // Nếu là MOMO, trả về link thanh toán
            switch ($request->payment_method) {
                case 'momo':
                    $momoResponse = $this->initiateMomoPayment($order, $order->total);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán MOMO',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $momoResponse['payUrl'],
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);

                case 'cod':
                    // Gửi email xác nhận cho COD

                    Mail::to($request->customer_email)->queue(new OrderPlaced($order, $user));
                    return response()->json([
                        'message' => 'Đặt hàng COD thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => null,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);
                default:
                    return response()->json([
                        'message' => 'Đặt hàng thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không thể tạo đơn hàng',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    /**
     * Xử lý thanh toán MOMO
     */
    public function processMomoPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Validate đầu vào
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Lấy giỏ hàng và chỉ lấy item selected = 1
            $cart = Cart::with(['items' => function($q) {
                $q->where('selected', true);
            }, 'items.variant'])->where('user_id', $user->id)->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
            }

            // Tính tổng
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
            }

            $shipping = 20000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;

            $order = $user->orders()->create([
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
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'sale_price' => $item->variant->sale_price,
                    'color_id' => $item->variant->color_id,
                    'size_id' => $item->variant->size_id,
                ]);
            }

            // Gọi API MoMo
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

        // Danh sách các trường cần kiểm tra và xác minh
        $requiredFields = [
            'amount', 'message', 'orderId', 'orderInfo',
            'orderType', 'partnerCode', 'payType', 'requestId',
            'responseTime', 'resultCode', 'transId', 'signature'
        ];

        // Kiểm tra thiếu trường
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::error("Thiếu trường {$field} trong dữ liệu MoMo", ['data' => $data]);
                return response()->json(['message' => "Thiếu trường {$field}"], 400);
            }
        }

        // Xử lý extraData rỗng
        $extraData = $data['extraData'] ?? '';

        // Tạo chuỗi raw hash
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
            Log::error('Xác minh chữ ký MOMO thất bại', [
                'calculated' => $calculatedSignature,
                'received' => $data['signature'],
                'rawHash' => $rawHash,
                'data' => $data
            ]);
            return response()->json(['message' => 'Chữ ký không hợp lệ'], 403);
        }

        Log::info('✅ Xác minh chữ ký MOMO thành công');

        // Xử lý đơn hàng
        $orderParts = explode('-', $data['orderId']);
        $orderId = $orderParts[0] ?? null;

        if (!$orderId) {
            Log::error('Không thể trích xuất orderId từ chuỗi', ['orderId_raw' => $data['orderId']]);
            return response()->json(['message' => 'orderId không hợp lệ'], 400);
        }

        $order = Order::with(['items.productVariant.stock'])->find($orderId);

        if (!$order) {
            Log::error('Không tìm thấy đơn hàng', ['order_id' => $orderId]);
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
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

                foreach ($order->items as $item) {
                    $item->variant->stock()->decrement('quantity', $item->quantity);
                }

                // Xóa sản phẩm đã mua khỏi giỏ hàng
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
                // Thanh toán thất bại
                $order->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);

                DB::commit();
                return response()->json(['message' => 'Thanh toán thất bại'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xử lý webhook MOMO: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi xử lý webhook'], 500);
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
            return response()->json(['message' => 'Tham số không hợp lệ'], 400);
        }

        // Trích xuất ID đơn hàng (định dạng: orderId-thời gian)
        $orderParts = explode('-', $orderId);
        $orderId = $orderParts[0];
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ((int) $resultCode === 0) {
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

        // Nếu cần hoàn tiền MOMO, gọi hàm hoàn tiền
        if ($order->payment_method === 'momo' && $order->payment_status === 'paid') {
            $refundResponse = $this->refundMomoPayment($order);
            if (!$refundResponse['success']) {
                throw new \Exception('Hoàn tiền MOMO thất bại: ' . $refundResponse['message']);
            }
        }
    }

    /**
     * Xử lý thanh toán VNPay
     */
    public function processVnpayPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Validate đầu vào
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Lấy giỏ hàng và chỉ lấy item selected = 1
            $cart = Cart::with(['items' => function($q) {
                $q->where('selected', true);
            }, 'items.variant'])->where('user_id', $user->id)->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
            }

            // Tính tổng
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
            }

            $shipping = 20000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;

            $order = $user->orders()->create([
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => 'vnpay',
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

            // Gọi API VNPay
            $vnpResponse = $this->initiateVnpayPayment($order);

            DB::commit();

            return response()->json([
                'message' => 'Đã khởi tạo thanh toán VNPay',
                'data' => [
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                    'payment_url' => $vnpResponse['payment_url'],
                    'order_id' => $order->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo VNPay: ' . $e->getMessage());

            return response()->json([
                'message' => 'Lỗi khởi tạo thanh toán VNPay',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Khởi tạo thanh toán VNPay
     */
    protected function initiateVnpayPayment($order)
    {
        try {
            $vnp_TmnCode = env('VNP_TMN_CODE'); // Mã website do VNPay cấp
            $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật
            $vnp_Url = env('VNP_URL'); // URL VNPay
            $vnp_ReturnUrl = env('VNP_RETURN_URL'); // URL callback sau thanh toán

            // Tạo mã đơn hàng duy nhất
            $vnp_TxnRef = $order->id . '_' . time();
            $vnp_OrderInfo = 'Thanh toan hoa don ' . $order->order_number;
            $vnp_OrderType = 'other';
            $vnp_Amount = $order->total * 100; // Nhân 100 theo yêu cầu VNPay
            $vnp_Locale = 'vn';
            $vnp_BankCode = 'VNBANK'; // Có thể để rỗng nếu không ép chọn ngân hàng
            $vnp_IpAddr = request()->ip(); // IP khách hàng

            // Danh sách các tham số gửi sang VNPay
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

            // Optional fields
            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            } else {
                // Bỏ qua mã ngân hàng và để VNPAY tự động chọn
                unset($inputData['vnp_BankCode']);
            }

            // Sort parameters by key
            ksort($inputData);

            // Build the query string and hashdata for signature
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

            // Remove trailing '&' from the query string
            $queryString = rtrim($queryString, '&');


            // Now calculate the secure hash using the secret key
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

            // Append the secure hash to the query string
            $vnp_Url .= "?" . $queryString . "&vnp_SecureHash=" . $vnpSecureHash;
            return [
                'payment_url' => $vnp_Url,
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi tạo link thanh toán VNPay: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }



    /**
     * Xử lý IPN từ VNPay
     */
    public function vnpayIpn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            // Bỏ các tham số không dùng để tạo chữ ký
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);

            // Sắp xếp dữ liệu theo key
            ksort($inputData);

            // Tạo chuỗi hashdata giống như lúc gửi đi
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

            // So sánh chữ ký
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            if ($secureHash !== $vnp_SecureHash) {
                return response()->json(['RspCode' => '97', 'Message' => 'Sai Checksum']);
            }

            // Tách ID đơn hàng từ vnp_TxnRef
            $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            $orderId = $orderParts[0] ?? null;

            if (!$orderId || !is_numeric($orderId)) {
                return response()->json(['RspCode' => '01', 'Message' => 'Không tìm thấy đơn hàng']);
            }

            $order = Order::with(['items.productVariant.stock'])->find($orderId);
            if (!$order) {
                return response()->json(['RspCode' => '01', 'Message' => 'Không tìm thấy đơn hàng']);
            }

            // Kiểm tra trạng thái giao dịch
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

                    // Xóa item đã mua khỏi giỏ hàng
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        foreach ($order->items as $item) {
                            CartItem::where('cart_id', $cart->id)
                                ->where('product_variant_id', $item->product_variant_id)
                                ->where('selected', true)
                                ->delete();
                        }
                    }

                    // Gửi mail xác nhận
                    Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                    DB::commit();
                    return response()->json(['RspCode' => '00', 'Message' => 'Thanh toán thành công']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Lỗi xử lý IPN VNPay: ' . $e->getMessage(), ['exception' => $e]);
                    return response()->json(['RspCode' => '99', 'Message' => 'Lỗi xử lý giao dịch']);
                }
            }

            // Giao dịch không thành công
            return response()->json(['RspCode' => '02', 'Message' => 'Giao dịch không thành công']);
        } catch (\Exception $e) {
            Log::error('Lỗi hệ thống IPN VNPay: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['RspCode' => '99', 'Message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Xử lý trả về từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            // Bỏ các trường không dùng để tạo chữ ký
            unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

            // Sắp xếp các tham số theo thứ tự key
            ksort($inputData);

            // Tạo chuỗi hashData giống như khi gửi
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

            // Tính toán lại chữ ký
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Tách orderId từ vnp_TxnRef
            $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            $orderId = $orderParts[0] ?? null;

            if (!$orderId || !is_numeric($orderId)) {
                return response()->json(['message' => 'Không tìm thấy đơn hàng'], 400);
            }

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json(['message' => 'Đơn hàng không tồn tại'], 400);
            }

            // Kiểm tra chữ ký và phản hồi
            if ($secureHash === $vnp_SecureHash) {
                if ($inputData['vnp_ResponseCode'] === '00') {
                    return response()->json([
                        'message' => 'Thanh toán thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'payment_status' => $order->payment_status,
                            'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                        ]
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Giao dịch không thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'payment_status' => $order->payment_status,
                            'response_code' => $inputData['vnp_ResponseCode'],
                        ]
                    ], 400);
                }
            } else {
                return response()->json(['message' => 'Sai checksum'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Lỗi xử lý return URL VNPay: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi hệ thống'], 500);
        }
    }

    /**
     * Hoàn tiền MOMO
     */
    protected function refundMomoPayment(Order $order, $amount = null)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/refund";
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
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
            return ['success' => false, 'message' => $response->json()['message'] ?? 'Lỗi không xác định'];
        }
    }

    public function checkReceivedProduct(Request $request)  // Kiểm tra xem người dùng đã nhận sản phẩm chưa
    {
        $productId = $request->query('product_id');
        $user = auth()->user();

        $hasReceived = OrderItem::where('product_variant_id', $productId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'completed');
            })->exists();

        return response()->json(['received' => $hasReceived]);
    }

    /**
     * Xử lý hoàn trả đơn hàng
     */
    public function returnOrder(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'return_items' => 'required|array|min:1',
            'return_items.*.order_item_id' => 'required|exists:order_items,id',
            'return_items.*.quantity' => 'required|integer|min:1',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra quyền (chỉ admin hoặc user sở hữu đơn hàng)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        // Chỉ cho phép hoàn trả đơn hàng đã giao
        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Chỉ có thể hoàn trả đơn hàng đã giao'], 400);
        }

        DB::beginTransaction();

        try {
            // Cập nhật số lượng hoàn trả và lý do
            foreach ($request->return_items as $returnItem) {
                $orderItem = OrderItem::find($returnItem['order_item_id']);

                // Kiểm tra số lượng hợp lệ
                if ($returnItem['quantity'] > $orderItem->quantity) {
                    throw new \Exception("Số lượng hoàn trả vượt quá số lượng đã mua");
                }

                // Hoàn lại tồn kho
                $orderItem->productVariant->stock()->increment('quantity', $returnItem['quantity']);

                // Đánh dấu sản phẩm đã hoàn trả
                $orderItem->update([
                    'returned_quantity' => $returnItem['quantity'],
                    'return_reason' => $request->reason,
                ]);
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'returned',
                'refund_amount' => $request->refund_amount ?? $order->total,
            ]);

            // Nếu cần hoàn tiền (MOMO/VNPay)
            if ($order->payment_method !== 'cod' && $order->payment_status === 'paid') {
                $refundResponse = $this->refundPayment($order, $request->refund_amount);
                if (!$refundResponse['success']) {
                    throw new \Exception('Hoàn tiền thất bại: ' . $refundResponse['message']);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Yêu cầu hoàn trả thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi hoàn trả đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xử lý hoàn trả'], 500);
        }
    }

    public function confirmReceived($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Chỉ cho phép xác nhận khi trạng thái là 'shipped'
        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Không thể xác nhận đơn hàng này'], 400);
        }

        $order->status = 'completed'; // Đã nhận hàng ( là hoàn thành)
        $order->completed_at = now();

        // Nếu phương thức thanh toán là COD => khi nhận hàng => đã thanh toán
        if ($order->payment_method === 'cod') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json(['message' => 'Đã xác nhận nhận hàng thành công']);
    }

    // Yêu cầu trả hàng
public function requestReturn(Request $request, $id)
{
    $order = Order::findOrFail($id);

    if ($order->user_id !== auth()->id()) {
        return response()->json(['message' => 'Không có quyền truy cập'], 403);
    }

    $request->validate([
        'reason' => 'required|string|max:255',
        'media' => 'nullable', // Có thể là ảnh hoặc video
        'media.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240', // Tối đa 10MB
    ]);

    if ($order->status !== 'shipped') {
        return response()->json(['message' => 'Chỉ có thể yêu cầu hoàn hàng khi đơn đã giao hàng'], 400);
    }

    $order->status = 'return_requested';
    $order->return_reason = $request->input('reason');
    $order->return_requested_at = now();

    // Xử lý nhiều file upload
    $mediaPaths = [];
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $mediaPaths[] = $file->store('returns', 'public');
        }
        $order->return_media = json_encode($mediaPaths);
    }

    $order->save();

    return response()->json(['message' => 'Yêu cầu hoàn hàng đã được gửi!']);
}
}
