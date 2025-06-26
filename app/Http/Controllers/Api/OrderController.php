<?php

namespace app\Http\Controllers\Api;

use app\Models\Order;
use app\Http\Controllers\Controller;
use App\Mail\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;



class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'shipping' => 'nullable|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_status' => 'nullable|string',
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'] ?? 0,
                'shipping' => $validated['shipping'] ?? 0,
                'total' => $validated['total'],
                'status' => $validated['status'] ?? 'pending',
                'payment_method' => $validated['payment_method'] ?? 'cod',
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json(['message' => 'Đặt hàng thành công', 'order' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đặt hàng thất bại', 'error' => $e->getMessage()], 500);
        }
    }
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orders = $user->orders()
            ->with(['items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }


    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']);

        return response()->json($order);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cod,momo',
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:255',
            // 'discount_code' => 'nullable|string|max:50',
        ]);

        $paymentMethod = $request->payment_method;
        if (!in_array($paymentMethod, ['cod', 'momo'])) {
            return response()->json(['message' => 'Phương thức thanh toán không hợp lệ'], 400);
        }

        // Kiểm tra người dùng đã đăng nhập
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thanh toán'], 401);
        }
        $user = Auth::user();  // Lấy thông tin người dùng đã đăng nhập

        $cart = Cart::where('user_id', $user->id) // Lấy giỏ hàng của người dùng
            ->with([
                'items.variant.product',
                'items.variant.color',
                'items.variant.size',
                'items.variant.stock'
            ])
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);  // Kiểm tra xem giỏ hàng có tồn tại không
        }

        $selectedItems = $cart->items->where('selected', true);  // Lấy các sản phẩm đã chọn trong giỏ hàng

        if ($selectedItems->isEmpty()) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán'], 400); // Kiểm tra xem có sản phẩm nào được chọn không
        }

        // Bắt đầu transaction để đảm bảo dữ liệu an toàn
        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho và chuẩn bị cập nhật
            $stockDecrements = [];

            foreach ($selectedItems as $item) {
                $availableStock = $item->variant->stock->quantity ?? 0;
                if ($availableStock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Không đủ tồn kho cho sản phẩm: ' . $item->variant->product->name,
                        'variant_id' => $item->variant->id,
                        'available_stock' => $availableStock,
                    ], 400);
                }
                $stockDecrements[$item->variant->stock->id] = $item->quantity;
            }

            // Tính toán
            $subtotal = $selectedItems->sum(fn($item) => ($item->variant->sale_price ?? $item->variant->price) * $item->quantity);
            $shipping = 20000;
            $tax = $subtotal * 0.1;
            $discount = 0;

            // Xử lý mã giảm giá (giả định đơn giản)
            // if ($request->filled('discount_code') && $request->discount_code === 'GIAM10') {
            // $discount = $subtotal * 0.1;
            // }

            $total = $subtotal + $shipping + $tax - $discount;

            // Tạo đơn hàng
            $order = $user->orders()->create([
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            // Lưu item vào đơn hàng
            foreach ($selectedItems as $item) {
                $order->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'sale_price' => $item->variant->sale_price,
                    'color_id' => $item->variant->color_id,
                    'size_id' => $item->variant->size_id,
                ]);
            }

            // Trừ tồn kho (batch theo stock_id)
            foreach ($stockDecrements as $stockId => $qty) {
                \App\Models\Stock::where('id', $stockId)->decrement('quantity', $qty);
            }

            // Xoá khỏi giỏ hàng
            CartItem::where('cart_id', $cart->id)->where('selected', true)->delete();

            DB::commit();

            // Gửi mail (dùng queue nếu config queue)
            // Mail::to($user->email)->queue(new OrderPlaced($order, $selectedItems));
            Mail::to($user->email)->send(new OrderPlaced($order));


            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => $order->load('items.variant.product', 'items.variant.color', 'items.variant.size'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi đặt hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
