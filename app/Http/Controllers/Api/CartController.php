<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlaced;
use App\Models\User;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Stock;



class CartController extends Controller
{
    public function addToCart(CartRequest $request)
    {
        $request->validate([
            'product_variant_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'note' => 'nullable|string|max:255',
        ]);

        $variant = ProductVariant::where('id', $request->product_variant_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        if (!$variant) {
            return response()->json(['message' => 'Biến thể sản phẩm không hợp lệ.'], 400);
        }

        $stock = Stock::where('product_variant_id', $variant->id)->value('quantity');

        if ($stock === null) {
            return response()->json(['message' => 'Không tìm thấy thông tin tồn kho.'], 404);
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        $totalQuantity = $request->quantity + ($item->quantity ?? 0);

        if ($totalQuantity > $stock) {
            return response()->json(['message' => 'Số lượng vượt quá tồn kho'], 400);
        }

        if ($item) {
            $item->quantity = $totalQuantity;
            $item->note = $request->note ?? $item->note;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity' => $request->quantity,
                'color_id' => $request->color_id,
                'size_id' => $request->size_id,
                'note' => $request->note,
                'selected' => false,
            ]);
        }

        return response()->json(['message' => 'Đã thêm sản phẩm vào giỏ hàng.']);
    }

    public function viewCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['cart_items' => []]);
        }

        $items = CartItem::with(['productVariant.product', 'productVariant.color', 'productVariant.size', 'productVariant.stock'])
            ->where('cart_id', $cart->id)
            ->get()
            ->map(function ($item) {
                $variant = $item->productVariant;
                $stock = $variant->stock->quantity ?? 0;

                return [
                    'id' => $item->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'image' => $variant->thumbnail,
                    'color' => optional($variant->color)->name,
                    'color_id' => $variant->color_id,
                    'size' => optional($variant->size)->name,
                    'size_id' => $variant->size_id,
                    'price' => $variant->sale_price ?? $variant->price,
                    'quantity' => $item->quantity,
                    'stock' => $stock,
                    'subtotal' => $item->quantity * ($variant->sale_price ?? $variant->price),
                    'selected' => $item->selected,
                    'note' => $item->note,
                ];
            });

        return response()->json(['cart_items' => $items]);
    }

    public function updateQuantity(CartRequest $request, $item_id)
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'selected' => 'sometimes|boolean',
            'note' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $validVariant = ProductVariant::where('id', $item->product_variant_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        if (!$validVariant) {
            return response()->json(['message' => 'Màu sắc hoặc kích thước không hợp lệ.'], 400);
        }

        // Cập nhật variant nếu khác
        if ($item->product_variant_id !== $validVariant->id) {
            $item->product_variant_id = $validVariant->id;
        }

        $item->update($request->only(['quantity', 'selected', 'note']));

        return response()->json(['message' => 'Đã cập nhật sản phẩm trong giỏ.']);
    }

    public function updateSelected(Request $request, $item_id)
    {
        $request->validate(['selected' => 'required|boolean']);

        $user = Auth::user();
        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $item->selected = $request->selected;
        $item->save();

        return response()->json(['message' => 'Cập nhật chọn sản phẩm thành công.']);
    }

    public function removeFromCart($item_id)
    {
        $user = Auth::user();

        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng.']);
    }
    public function removeSelectedItems()
    {
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Không tìm thấy giỏ hàng.'], 404);
        }

        CartItem::where('cart_id', $cart->id)
            ->where('selected', true)
            ->delete();

        return response()->json(['message' => 'Đã xoá các sản phẩm đã chọn khỏi giỏ hàng.']);
    }
    public function clearCart()
    {
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }

        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng.']);
    }


    public function getCartTotal()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['total' => 0]);
        }

        $items = CartItem::with(['productVariant'])
            ->where('cart_id', $cart->id)
            ->where('selected', true)
            ->get();

        $total = $items->sum(
            fn($item) => ($item->productVariant->sale_price ?? $item->productVariant->price) * $item->quantity
        );

        return response()->json(['total' => $total]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'payment_method' => 'required|string|in:cod,banking,momo',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
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
            $variant = ProductVariant::with('stock', 'product')->findOrFail($item['product_variant_id']);

            // Tránh race condition
            $affected = Stock::where('product_variant_id', $variant->id)
                ->where('quantity', '>=', $item['quantity'])
                ->decrement('quantity', $item['quantity']);

            if ($affected === 0) {
                return response()->json([
                    'message' => 'Không đủ hàng cho sản phẩm: ' . $variant->product->name,
                    'available_stock' => $variant->stock->quantity ?? 0
                ], 400);
            }

            $order->items()->create([
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'sale_price' => $variant->sale_price ?? null,
                'color_id' => $variant->color_id,
                'size_id' => $variant->size_id,
            ]);
        }

        // Xoá sản phẩm đã chọn khỏi giỏ
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->where('selected', true)->delete();
        }

        $order->loadMissing('items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size');
        Mail::to($user->email)->send(new OrderPlaced($order, $request->payment_method));


        return response()->json([
            'message' => 'Đặt hàng thành công!',
            'order' => $order,
        ]);
    }
}
