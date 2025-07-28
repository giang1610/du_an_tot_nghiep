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
use App\Events\FailProduct;
use App\Models\Voucher;
use App\Models\VoucherUser;
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
                    throw new \Exception(message: "Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
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
            event(new FailProduct($order->order_number, $order->id));


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

    // Lấy các sản phẩm được chọn để mua (selected = true)
    $cartItems = CartItem::with('productVariant')
        ->where('cart_id', $cart->id)
        ->where('selected', true)
        ->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
    }

    // Tạo mảng items từ cartItems
    $items = $cartItems->map(function($item) {
        return [
            'product_variant_id' => $item->product_variant_id,
            'quantity' => $item->quantity,
        ];
    })->toArray();

    // Gộp vào request để validate
    $request->merge(['items' => $items]);

    // Validate dữ liệu
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
        // Kiểm tra tồn kho
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

        // Tạo các order items
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

            // Trừ kho nếu thanh toán COD
            if ($request->payment_method === 'cod') {
                $variant->stock->decrement('quantity', $item['quantity']);

                // Broadcast cập nhật tồn kho
                broadcast(new ProductStockUpdated(
                    $variant->id,
                    $variant->fresh()->stock->quantity
                ));
            }
        }

        // Gửi event
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
                // Gửi email xác nhận đơn hàng COD
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

        $order->status = 'completed'; // Đã nhận hàng (coi là hoàn thành)
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

}
