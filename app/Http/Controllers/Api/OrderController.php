<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
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
        $orders = Auth::user()
            ->orders() // FIX: gọi hàm để trả về query builder
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
}
