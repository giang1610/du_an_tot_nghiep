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
use App\Models\ProductVariant;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'note' => 'nullable|string|max:255',
        ]);

        // Kiểm tra product_variant tồn tại và khớp với color_id và size_id
        $variant = ProductVariant::where('id', $request->product_variant_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        if (!$variant) {
            return response()->json([
                'message' => 'Sản phẩm không tồn tại hoặc biến thể sản phẩm không hợp lệ với màu sắc hoặc kích cỡ đã chọn.'
            ], 400);
        }

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $request->product_variant_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
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
                'color_id' => $request->color_id,
                'size_id' => $request->size_id,
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
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
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

        // Kiểm tra variant có khớp với color_id và size_id
        $validVariant = ProductVariant::where('id', $item->product_variant_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        if (!$validVariant) {
            return response()->json([
                'message' => 'Màu sắc hoặc kích thước không hợp lệ cho sản phẩm này.'
            ], 400);
        }

        // Cập nhật
        $item->update($request->only(['quantity', 'selected', 'note']));

        return response()->json(['message' => 'Đã cập nhật sản phẩm trong giỏ.']);
    }

    // tính tổng tiền của giỏ hàng
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
}