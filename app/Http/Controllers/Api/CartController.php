<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;


class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        // Tìm hoặc tạo giỏ hàng cho user
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Kiểm tra sản phẩm đã có trong giỏ chưa
        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_variant_id', $request->product_variant_id)
                        ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
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
                    'product_name' => $variant->product->name,
                    'image' => $variant->image,
                    'color' => optional($variant->color)->name,
                    'size' => optional($variant->size)->name,
                    'price' => $variant->sale_price ?? $variant->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * ($variant->sale_price ?? $variant->price),
                    'selected' => $item->selected,
                ];
            });

        return response()->json(['cart_items' => $items]);
    }

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

    public function updateQuantity(Request $request, $item_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $item = CartItem::where('id', $item_id)
            ->whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

        if (!$item) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['message' => 'Đã cập nhật số lượng.']);
    }

    public function getCartTotal()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['total' => 0]);
        }

        $items = CartItem::with('productVariant')
            ->where('cart_id', $cart->id)
            ->where('selected', true) // chỉ tính những món đã tick chọn
            ->get();

        $total = $items->sum(function ($item) {
            $price = $item->productVariant->sale_price ?? $item->productVariant->price;
            return $price * $item->quantity;
        });

        return response()->json(['total' => $total]);
    }

}


