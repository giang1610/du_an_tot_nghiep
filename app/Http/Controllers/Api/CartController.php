<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlaced;

class CartController extends Controller
{
    public function removeFromCart($item_id)
    {
        $user = Auth::user();

        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ.'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng.']);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->note = $request->note ?? $item->note;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
                'note' => $request->note,
                'selected' => true, // default chọn
            ]);
        }

        return response()->json(['message' => 'Đã thêm sản phẩm vào giỏ hàng.']);
    }

    public function viewCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['cart' => []]);
        }

        $items = CartItem::with(['productVariant.product', 'productVariant.color', 'productVariant.size'])
            ->where('cart_id', $cart->id)
            ->get()
            ->map(function ($item) {
                $variant = $item->productVariant;

                return [
                    'id' => $item->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'image' => $variant->image,
                    'color' => optional($variant->color)->name,
                    'size' => optional($variant->size)->name,
                    'price' => $variant->sale_price ?? $variant->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * ($variant->sale_price ?? $variant->price),
                    'selected' => $item->selected,
                    'note' => $item->note,
                ];
            });

        return response()->json(['cart_items' => $items]);
    }

    public function updateQuantity(Request $request, $item_id)
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'selected' => 'sometimes|boolean',
            'note' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $item->update($request->only(['quantity', 'selected', 'note']));

        return response()->json(['message' => 'Đã cập nhật sản phẩm trong giỏ.']);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->with(['items.variant.product'])->first();

        if (!$cart) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);
        }

        // $selectedItems = $cart->items->where('selected', true);
        $selectedItems = $cart->items;


        if ($selectedItems->isEmpty()) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán'], 400);
        }

        // Kiểm tra tồn kho
        foreach ($selectedItems as $item) {
            if ($item->variant->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Không đủ tồn kho cho ' . $item->variant->product->name,
                    'variant_id' => $item->variant->id,
                    'available_stock' => $item->variant->stock,
                ], 400);
            }
        }

        $subtotal = $selectedItems->sum(function ($item) {
            return ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
        });

        $shipping = 10;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $shipping + $tax;
        /** @var \App\Models\User $user */
        // Tạo đơn hàng
        $order = $user->orders()->create([
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'payment_method' => $request->payment_method ?? 'credit_card',
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address ?? $request->shipping_address,
            'customer_email' => $user->email,
            'customer_phone' => $request->customer_phone,
            'notes' => $request->notes,
        ]);

        //Gắn các item vào đơn hàng
        foreach ($selectedItems as $item) {
            $order->items()->create([
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->variant->price,
                'sale_price' => $item->variant->sale_price,
                'color_id' => $item->variant->color_id,
                'size_id' => $item->variant->size_id,
            ]);

            $item->variant->decrement('stock', $item->quantity);
        }

        // Xoá item đã chọn
        CartItem::where('cart_id', $cart->id)->where('selected', true)->delete();

        // Gửi mail xác nhận
        $order->loadMissing('items.variant.product', 'items.variant.color', 'items.variant.size');
        Mail::to($user->email)->send(new OrderPlaced($order));

        // Trả về thông tin đơn hàng
        return response()->json([
            'message' => 'Đặt hàng thành công',
            'order' => $order->load('items.variant.product', 'items.variant.color', 'items.variant.size'),
        ]);
    }
}

