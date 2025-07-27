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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CartItem;
use App\Events\ProductStockUpdated;
use App\Events\NewOrderCreated;
use App\Models\Voucher;
use App\Services\Payment\CodPaymentService;
use App\Services\Payment\MomoPaymentService;
use App\Services\Payment\VnpayPaymentService;

class OrderController extends Controller
{
    protected $paymentServices = [
        'cod' => CodPaymentService::class,
        'momo' => MomoPaymentService::class,
        'vnpay' => VnpayPaymentService::class,
    ];

    public function checkout(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Không tìm thấy giỏ hàng.'], 404);
        }

        $cartItems = CartItem::with('productVariant')
            ->where('cart_id', $cart->id)
            ->where('selected', true)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
        }

        $items = $cartItems->map(function($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $request->merge(['items' => $items]);

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
                'payment_status' => $request->payment_method === 'cod' ? 'unpaid' : 'pending',
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
                    broadcast(new ProductStockUpdated(
                        $variant->id,
                        $variant->fresh()->stock->quantity
                    ));
                }
            }

            event(new NewOrderCreated($order->order_number, $order->id));

            // Xử lý thanh toán
            $paymentService = app($this->paymentServices[$request->payment_method]);
            $result = $paymentService->process($order);

            DB::commit();
            return response()->json($result);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không thể tạo đơn hàng',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function validateVoucher(Request $request)
    {
        // Logic validate voucher giữ nguyên như cũ
        $request->validate([
            'voucher_code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        try {
            $voucher = Voucher::where('code', $request->voucher_code)->first();

            if (!$voucher) {
                return response()->json(['message' => 'Voucher không tồn tại'], 400);
            }

            // Kiểm tra thời hạn voucher
            $now = now();
            if ($voucher->start_date && $now->lt($voucher->start_date)) {
                return response()->json(['message' => 'Voucher chưa có hiệu lực'], 400);
            }

            if ($voucher->end_date && $now->gt($voucher->end_date)) {
                return response()->json(['message' => 'Voucher đã hết hạn'], 400);
            }

            // Kiểm tra số lượng
            if ($voucher->quantity !== null && $voucher->quantity <= 0) {
                return response()->json(['message' => 'Voucher đã hết lượt sử dụng'], 400);
            }

            // Kiểm tra điều kiện tối thiểu
            if ($voucher->min_order_amount && $request->subtotal < $voucher->min_order_amount) {
                return response()->json(['message' => 'Đơn hàng không đạt điều kiện áp dụng voucher'], 400);
            }

            // Tính toán giá trị giảm giá
            if ($voucher->discount_type === 'amount') {
                $discount = min($voucher->discount_amount, $request->subtotal);
            } else {
                $discount = $request->subtotal * ($voucher->discount_percent / 100);
                if ($voucher->max_discount) {
                    $discount = min($discount, $voucher->max_discount);
                }
            }

            return response()->json([
                'message' => 'Voucher hợp lệ',
                'discount' => $discount,
                'voucher' => $voucher
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi validate voucher: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi kiểm tra voucher'], 500);
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

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.'], 400);
        }

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

    public function confirmReceived($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Không thể xác nhận đơn hàng này'], 400);
        }

        $order->status = 'delivered';
        $order->delivered_at = now();

        if ($order->payment_method === 'cod') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json(['message' => 'Đã xác nhận nhận hàng thành công']);
    }

    public function requestReturn(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);

        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Chỉ có thể yêu cầu hoàn hàng khi đơn đã giao hàng'], 400);
        }

        $order->status = 'return_requested';
        $order->return_reason = $request->input('reason');
        $order->return_requested_at = now();

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('returns', 'public');
            $order->return_media = $path;
        }

        $order->save();

        return response()->json(['message' => 'Yêu cầu hoàn hàng đã được gửi!']);
    }
}