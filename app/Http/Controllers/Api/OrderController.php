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
            Mail::to($request->customer_email)->queue(new OrderPlaced($order, $order->user));

            DB::commit();

            return response()->json([
                'message' => 'Tạo đơn hàng thành công',
                'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size'])

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
            ->with(['items.variant.product', 'items.variant.color', 'items.variant.size'])

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

        $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']);


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

    /**
    * Xử lý thanh toán MOMO
    */
    protected function processMomoPayment($user, $request, $cart, $totals)
    {
    DB::beginTransaction();

    try {
        // Tạo đơn hàng tạm (chưa trừ tồn kho)
        $order = $user->orders()->create([
            'subtotal'         => $totals['subtotal'],
            'shipping'         => $totals['shipping'],
            'tax'              => $totals['tax'],
            'total'            => $totals['total'],
            'status'           => 'pending',
            'payment_method'   => 'momo',
            'payment_status'   => 'pending',
            'shipping_address' => $request->shipping_address,
            'billing_address'  => $request->billing_address ?? $request->shipping_address,
            'customer_email'   => $user->email,
            'customer_phone'   => $request->customer_phone,
            'notes'            => $request->notes,
        ]);

        // Tạo các item cho đơn hàng (chưa trừ tồn kho)
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id'           => $order->id,
                'product_variant_id' => $item->product_variant_id,
                'quantity'           => $item->quantity,
                'price'              => $item->variant->price,
                'sale_price'         => $item->variant->sale_price,
                'color_id'           => $item->variant->color_id,
                'size_id'            => $item->variant->size_id,
            ]);
        }

        // Khởi tạo thanh toán MOMO (nên dùng requestType = captureWallet cho QR)
        $momoResponse = $this->initiateMomoPayment($order, $totals['total']);

        DB::commit();

        return response()->json([
            'message' => 'Đã khởi tạo thanh toán MOMO',
            'data' => [
                'order'       => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                'payment_url' => $momoResponse['payUrl'] ?? null,
                'order_id'    => $order->id,
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Lỗi khởi tạo MOMO: ' . $e->getMessage());
        return response()->json([
            'message' => 'Lỗi khởi tạo thanh toán MOMO',
            'error'   => $e->getMessage()
        ], 500);
    }
    }

    /**
    * Khởi tạo thanh toán MOMO
    */
    protected function initiateMomoPayment($order, $amount)
    {


        foreach ($config as $key => $value) {
            if (empty($value)) {
                throw new \Exception("Thiếu cấu hình MOMO: $key");
            }
        }

        $requestId = time() . "";
        $orderId = $order->id . '-' . time();
        $orderInfo = "Thanh toán đơn hàng #{$order->order_number}";
        $requestType = "payWithATM";
        $extraData = "";

        // Tạo rawHash đúng thứ tự tài liệu MOMO
        $rawHash = "accessKey=" . $config['access_key'] . 
                "&amount=" . $amount . 
                "&extraData=" . $extraData . 
                "&ipnUrl=" . $config['ipn_url'] . 
                "&orderId=" . $orderId . 
                "&orderInfo=" . $orderInfo . 
                "&partnerCode=" . $config['partner_code'] . 
                "&redirectUrl=" . $config['redirect_url'] . 
                "&requestId=" . $requestId . 
                "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $config['secret_key']);

        $data = [
                'partnerCode' => $config['partner_code'],
                'partnerName' => env('APP_NAME', 'MG Fashion Store'),
                'storeId' => 'MomoTestStore',
                'requestId' => $requestId,
                'amount' => (string)$amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $config['redirect_url'],
                'ipnUrl' => $config['ipn_url'],
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            ];

        $response = Http::timeout(30)->post($config['api_url'], $data);

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
        $secretKey = env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        // Kiểm tra các trường bắt buộc
        $requiredFields = [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 
            'orderInfo', 'orderType', 'transId', 'resultCode', 'payType', 
            'responseTime', 'signature'
        ];

        // Các trường có thể thiếu, gán mặc định rỗng
        $optionalFields = [
        'extraData'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Log::error('Thiếu trường bắt buộc trong webhook Momo', ['field' => $field]);
                return response()->json(['message' => "Thiếu trường bắt buộc: $field"], 400);
            }
        }
        foreach ($optionalFields as $field) {
            if (!isset($data[$field])) $data[$field] = '';
        }

        // Tạo rawHash đúng thứ tự tài liệu MOMO (KHÔNG encode/decode lại orderInfo)
        $rawHash = "partnerCode=" . $data['partnerCode'] . 
                "&accessKey=" . $data['accessKey'] . 
                "&requestId=" . $data['requestId'] . 
                "&amount=" . $data['amount'] . 
                "&orderId=" . $data['orderId'] . 
                "&orderInfo=" . $data['orderInfo'] . 
                "&orderType=" . $data['orderType'] . 
                "&transId=" . $data['transId'] . 
                "&responseTime=" . $data['responseTime'] . 
                "&errorCode=" . ($data['errorCode'] ?? '') . 
                "&payType=" . $data['payType'] . 
                "&extraData=" . $data['extraData'];

        $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);
        // Kiểm tra signature
        if ($partnerSignature !== $data['signature']) {
            Log::error('Signature không hợp lệ', [
                'received' => $data['signature'],
                'calculated' => $partnerSignature,
                'rawHash' => $rawHash
            ]);
            return response()->json(['message' => 'Signature không hợp lệ'], 403);
        }

        // Trích xuất ID đơn hàng (định dạng: orderId-thời gian)
        $orderParts = explode('-', $data['orderId']);
        $orderId = $orderParts[0];
        $order = Order::with(['items.variant.stock'])->find($orderId);

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

                // Trừ tồn kho
                foreach ($order->items as $item) {
                    $item->variant->stock()->decrement('quantity', $item->quantity);
                }

                // Gửi email xác nhận
                Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                // // Xóa sản phẩm khỏi giỏ hàng
                // $cart = Cart::where('user_id', $order->user_id)->first();
                // if ($cart) {
                //     $cart->items()->whereIn('product_variant_id', $order->items->pluck('product_variant_id'))->delete();
                // }

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


    public function checkReceivedProduct(Request $request)
    {
        $productId = $request->query('product_id');
        $user = auth()->user();

        $hasReceived = OrderItem::where('product_variant_id', $productId)


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
