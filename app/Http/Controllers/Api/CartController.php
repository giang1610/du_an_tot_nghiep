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

        if ($stock <= 0) {
            return response()->json(['message' => 'Sản phẩm tạm thời hết hàng.'], 400);
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
}