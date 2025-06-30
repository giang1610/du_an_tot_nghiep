<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Mail\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Quy tắc validation chung cho đơn hàng
    protected $orderValidationRules = [
        'shipping_address' => 'required|string|max:255',
        'billing_address' => 'nullable|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    /**
     * Tạo đơn hàng mới từ API
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
                'message' => 'Lỗi xác thực',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho trước
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

            // Tạo items đơn hàng và cập nhật tồn kho
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

                // Cập nhật tồn kho
                if ($variant->stock) {
                    $variant->stock->decrement('quantity', $item['quantity']);
                }
            }

            // Gửi email xác nhận
            Mail::to($request->customer_email)->queue(new OrderPlaced($order));

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

    /**
     * Lấy danh sách đơn hàng của người dùng
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');

        $query = Auth::user()->orders()
            ->with(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])

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
    /**
     * Lấy chi tiết đơn hàng
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);


        return response()->json([
            'message' => 'Lấy thông tin đơn hàng thành công',
            'data' => $order
        ]);
    }
    /**
     * Cập nhật địa chỉ
     * 
     */
    public function updateAddress(Request $request, Order $order)
    {
        // Kiểm tra quyền truy cập, validate dữ liệu nếu cần
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


    /**
     * Hủy đơn hàng
     * 
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['message' => 'Hủy đơn hàng thành công']);
    }
    /**
     * Xử lý thanh toán (COD hoặc MOMO)
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'payment_method'   => 'required|string|in:cod,vnpay,momo',
            'shipping_address' => 'required|string',
            'customer_phone'   => 'required|string',
            'customer_email'   => 'required|email',
            'items'            => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal'         => 'required|numeric|min:0',
            'tax'              => 'required|numeric|min:0',
            'shipping'         => 'required|numeric|min:0',
            'total'            => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Tạo đơn hàng
            $order = Order::create([
                'user_id'          => $user->id,
                'order_number'     => 'ORD-' . strtoupper(uniqid()),
                'payment_method'   => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'customer_phone'   => $request->customer_phone,
                'customer_email'   => $request->customer_email,
                'subtotal'         => $request->subtotal,
                'tax'              => $request->tax,
                'shipping'         => $request->shipping,
                'total'            => $request->total,
                'status'           => 'pending',
            ]);

            foreach ($request->items as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);

                if (!$variant) {
                    throw new \Exception("Sản phẩm không tồn tại.");
                }

                if ($variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ hàng tồn kho.");
                }

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $item['quantity'],
                    'price'              => $variant->sale_price ?? $variant->price,
                ]);

                // Trừ kho
                $variant->stock->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'data' => [
                    'order_id' => $order->id,
                    'payment_url' => null, // nếu là COD thì không cần redirect
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Không thể tạo đơn hàng',
                'error'   => $e->getMessage(),
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
            Mail::to($user->email)->queue(new OrderPlaced($order));

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng COD thành công',
                'data' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])

            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thanh toán COD: ' . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi thanh toán COD',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý thanh toán MOMO
     */
    protected function processMomoPayment($user, $request, $cart, $totals)
    {
        DB::beginTransaction();

        try {

            // Tạo đơn hàng tạm (chưa trừ tồn kho)
            $user = Auth::user(); // hoặc User::find($id)

            if (!$user) {
                return response()->json(['message' => 'Unauthorized or user not found'], 401);
            }
            $order = $user->orders()->create([
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'status' => 'pending',
                'payment_method' => 'momo',
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            // Tạo items đơn hàng (chưa trừ tồn kho)
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

            // Khởi tạo thanh toán MOMO
            $momoResponse = $this->initiateMomoPayment($order, $totals['total']);

            // Chưa xóa giỏ hàng - chờ xác nhận từ MOMO
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
     * Xử lý webhook thông báo từ MOMO
     */
    public function momoWebhook(Request $request)
    {
        $data = $request->all();
        $secretKey = env('MOMO_SECRET_KEY');

        // Xác minh chữ ký
        $rawHash = "accessKey={$data['accessKey']}&amount={$data['amount']}&extraData={$data['extraData']}&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&transId={$data['transId']}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        if ($signature !== $data['signature']) {
            Log::error('Xác minh chữ ký MOMO thất bại', [
                'received' => $data['signature'],
                'calculated' => $signature
            ]);
            return response()->json(['message' => 'Chữ ký không hợp lệ'], 403);
        }

        // Trích xuất ID đơn hàng (định dạng: orderId-thời gian)
        $orderParts = explode('-', $data['orderId']);
        $orderId = $orderParts[0];
        $order = Order::with(['items.variant.stock', 'user'])->find($orderId);

        if (!$order) {
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

                // Trừ tồn kho
                foreach ($order->items as $item) {
                    $item->variant->stock()->decrement('quantity', $item->quantity);
                }

                // Xóa sản phẩm khỏi giỏ hàng
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    $cart->items()->whereIn('product_variant_id', $order->items->pluck('product_variant_id'))->delete();
                }

                // Gửi email xác nhận
                Mail::to($order->customer_email)->queue(new OrderPlaced($order));

                DB::commit();
                return response()->json(['message' => 'Xử lý thanh toán thành công']);
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
            Log::error('Lỗi xử lý webhook MOMO: ' . $e->getMessage());
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

        if (!$orderId || !$resultCode) {
            return response()->json(['message' => 'Tham số không hợp lệ'], 400);
        }

        // Trích xuất ID đơn hàng (định dạng: orderId-thời gian)
        $orderParts = explode('-', $orderId);
        $orderId = $orderParts[0];
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ((int)$resultCode === 0) {
            return response()->json([
                'message' => 'Thanh toán thành công',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                ]
            ]);
        }

        return response()->json([
            'message' => 'Thanh toán thất bại hoặc đã hủy',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
            ]
        ], 400);
    }

    /**
     * Khởi tạo thanh toán MOMO
     */
    protected function initiateMomoPayment($order, $amount)
    {
        $config = [
            'api_url' => env('MOMO_API_URL', 'https://test-payment.momo.vn/v2/gateway/api/create'),
            'partner_code' => env('MOMO_PARTNER_CODE'),
            'access_key' => env('MOMO_ACCESS_KEY'),
            'secret_key' => env('MOMO_SECRET_KEY'),
            'redirect_url' => env('MOMO_REDIRECT_URL', url('/api/orders/momo/return')),
            'ipn_url' => env('MOMO_IPN_URL', url('/api/orders/momo/webhook')),
        ];

        // Kiểm tra cấu hình
        foreach ($config as $key => $value) {
            if (empty($value)) {
                throw new \Exception("Thiếu cấu hình MOMO: $key");
            }
        }

        $requestId = Str::uuid();
        $orderId = $order->id . '-' . time();
        $orderInfo = "Thanh toán cho đơn hàng #{$order->id}";

        $rawHash = "accessKey={$config['access_key']}&amount={$amount}&extraData=&ipnUrl={$config['ipn_url']}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$config['partner_code']}&redirectUrl={$config['redirect_url']}&requestId={$requestId}&requestType=payWithATM";
        $signature = hash_hmac('sha256', $rawHash, $config['secret_key']);

        $requestData = [
            'partnerCode' => $config['partner_code'],
            'partnerName' => env('APP_NAME', 'Cửa hàng của bạn'),
            'storeId' => 'MOMO_STORE',
            'requestId' => $requestId,
            'amount' => (string)$amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $config['redirect_url'],
            'ipnUrl' => $config['ipn_url'],
            'lang' => 'vi',
            'extraData' => '',
            'requestType' => 'payWithATM',
            'signature' => $signature,
        ];

        $response = Http::timeout(30)->post($config['api_url'], $requestData);

        if (!$response->successful()) {
            throw new \Exception('Lỗi kết nối MOMO API: ' . $response->body());
        }

        $responseData = $response->json();

        if (!isset($responseData['payUrl'])) {
            throw new \Exception($responseData['message'] ?? 'Khởi tạo thanh toán MOMO thất bại');
        }

        return $responseData;
    }
    public function checkReceivedProduct(Request $request)
    {
        $productId = $request->query('product_id');
        $user = auth()->user();

        $hasReceived = OrderItem::where('product_variant_id', $productId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'delivered');
            })->exists();

        return response()->json(['received' => $hasReceived]);
    }
    public function payViaMomo(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'notes'            => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'subtotal' => 'required|numeric|min:0',
            'tax'      => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total'    => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Giả lập giỏ hàng tạm từ request
            $cart = new \stdClass();
            $cart->items = [];

            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm #{$item['product_variant_id']}");
                }

                $cartItem = new \stdClass();
                $cartItem->product_variant_id = $item['product_variant_id'];
                $cartItem->quantity = $item['quantity'];
                $cartItem->variant = $variant;

                $cart->items[] = $cartItem;
            }

            $totals = [
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'shipping' => $request->shipping ?? 0,
                'total' => $request->total,
            ];

            return $this->processMomoPayment($user, $request, $cart, $totals);
        } catch (\Exception $e) {
            \Log::error('Lỗi thanh toán MOMO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không thể khởi tạo thanh toán MOMO',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
