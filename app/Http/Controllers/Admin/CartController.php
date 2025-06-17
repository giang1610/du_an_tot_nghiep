<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Xem giỏ hàng
    public function viewCart()
    {
        $cart = $this->getOrCreateCart();
        $items = $this->getCartItems($cart);
        
        return view('cart.view', [
            'items' => $items,
            'total' => $this->calculateTotal($items)
        ]);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cart = $this->getOrCreateCart();
        $this->addOrUpdateCartItem($cart, $request->all());

        return redirect()->route('cart.view')
            ->with('success', 'Sản phẩm đã được thêm vào giỏ hàng');
    }

    // Cập nhật giỏ hàng
    public function updateQuantity(Request $request, CartItem $item)
    {
        $this->authorize('update', $item);

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|integer|min:1',
            'selected' => 'sometimes|boolean',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $item->update($request->only(['quantity', 'selected', 'note']));

        return redirect()->route('cart.view')
            ->with('success', 'Giỏ hàng đã được cập nhật');
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart(CartItem $item)
    {
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()->route('cart.view')
            ->with('success', 'Sản phẩm đã được xóa khỏi giỏ hàng');
    }

    // Trang thanh toán
    public function showCheckout()
    {
        $cart = $this->getOrCreateCart();
        $items = $this->getCartItems($cart->where('selected', true));
        
        if ($items->isEmpty()) {
            return redirect()->route('cart.view')
                ->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán');
        }

        return view('checkout.show', [
            'items' => $items,
            'total' => $this->calculateTotal($items),
            'user' => Auth::user()
        ]);
    }

    // Xử lý thanh toán
    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        $cart = $this->getOrCreateCart();
        $items = $this->getCartItems($cart->where('selected', true));

        if ($items->isEmpty()) {
            return redirect()->route('cart.view')
                ->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán');
        }

        // Validate thông tin thanh toán
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'payment_method' => 'required|in:cod,credit_card,bank_transfer',
        ]);

        if ($validator->fails()) {
            return redirect()->route('checkout.show')
                ->withErrors($validator)
                ->withInput();
        }

        // Kiểm tra tồn kho
        foreach ($items as $item) {
            if ($item->variant->stock < $item->quantity) {
                return redirect()->route('cart.view')
                    ->with('error', 'Sản phẩm "'.$item->variant->product->name.'" chỉ còn '.$item->variant->stock.' sản phẩm');
            }
        }

        // Xử lý đặt hàng trong transaction
        $order = DB::transaction(function() use ($user, $request, $items) {
            // Tính toán tổng tiền
            $subtotal = $this->calculateTotal($items);
            $shipping = 10000; // 10.000 VND
            $tax = $subtotal * 0.1; // 10% VAT
            $total = $subtotal + $shipping + $tax;

            // Tạo đơn hàng
            $order = $user->orders()->create([
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($items as $item) {
                $order->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'sale_price' => $item->variant->sale_price,
                    'color_id' => $item->variant->color_id,
                'size_id' => $item->variant->size_id,
                ]);

                $item->variant->decrement('stock', $item->quantity);
                $item->delete(); // Xóa khỏi giỏ hàng
            }

            return $order;
        });

        // Gửi email xác nhận
        $user->notify(new OrderConfirmation($order));

        return redirect()->route('order.confirmation', $order->id)
            ->with('success', 'Đặt hàng thành công!');
    }

    // ===== Các phương thức hỗ trợ =====
    
    protected function getOrCreateCart()
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }

    protected function getCartItems($cart)
    {
        return $cart->items()
            ->with(['variant.product', 'variant.color', 'variant.size'])
            ->get()
            ->map(function ($item) {
                $variant = $item->variant;
                $currentPrice = $variant->sale_price ?? $variant->price;
                
                return (object) [
                    'id' => $item->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'image' => $variant->image,
                    'color' => optional($variant->color)->name,
                    'size' => optional($variant->size)->name,
                    'price' => $currentPrice,
                    'quantity' => $item->quantity,
                    'subtotal' => $currentPrice * $item->quantity,
                    'selected' => $item->selected,
                    'note' => $item->note,
                    'variant' => $variant
                ];
            });
    }

    protected function calculateTotal($items)
    {
        return $items->sum('subtotal');
    }

    // Thêm hoặc cập nhật sản phẩm trong giỏ hàng
    protected function addOrUpdateCartItem($cart, $data)
    {
        $item = $cart->items()
            ->where('product_variant_id', $data['product_variant_id'])
            ->first();

        if ($item) {
            $item->quantity += $data['quantity'];
            $item->note = $data['note'] ?? $item->note;
            $item->save();
        } else {
            $cart->items()->create([
                'product_variant_id' => $data['product_variant_id'],
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
                'selected' => true,
            ]);
        }
    }


    // Trang xác nhận đơn hàng
    public function orderConfirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->route('cart.view')
                ->with('error', 'Bạn không có quyền truy cập vào đơn hàng này');
        }

        return view('order.confirmation', ['order' => $order]);
    }
}