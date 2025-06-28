<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use App\Mail\OrderPlaced;

class OrderController extends Controller
{
    // Lấy danh sách đơn hàng của người dùng
    public function index()
{
    $orders = Order::with([
        'items.productVariant.product',
        'items.color',
        'items.size'
    ])
    ->where('user_id', auth()->id()) // ✅ Lấy đơn theo user đang đăng nhập
    ->latest()
    ->get();

    return response()->json(['orders' => $orders], 200);
}


    // Xem chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with([
            'items.productVariant.product',
            'items.color',
            'items.size'
        ])
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json(['order' => $order], 200);
    }

    // Đặt hàng
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'payment_method' => 'required|in:cod,banking',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.color_id' => 'nullable|integer',
            'items.*.size_id' => 'nullable|integer',
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $subtotal = collect($request->items)->sum(fn($item) => $item['price'] * $item['quantity']);
            $shipping = 15000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'shipping_address' => $request->address,
                'billing_address' => $request->address,
                'customer_email' => $user->email,
                'customer_phone' => $request->phone,
                'notes' => $request->notes ?? null,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($request->items as $item) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);

                // Trừ kho
                if ($variant->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Sản phẩm không đủ hàng: ' . $variant->product->name
                    ], 400);
                }

                $variant->decrement('stock', $item['quantity']);

                $order->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sale_price' => $variant->sale_price,
                    'color_id' => $item['color_id'],
                    'size_id' => $item['size_id'],
                ]);
            }

            // Xóa các sản phẩm đã chọn khỏi giỏ
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->where('selected', true)->delete();
            }

            // Gửi email xác nhận đơn hàng
            Mail::to($user->email)->send(new OrderPlaced($order));

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order' => $order->load('items.productVariant.product', 'items.color', 'items.size')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Lỗi đặt hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi đặt hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
