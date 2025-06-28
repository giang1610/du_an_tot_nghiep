<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $orders = Order::with(['items.productVariant.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['orders' => $orders], 200);
    }
    // app/Http/Controllers/Api/OrderController.php

    public function show($id)
    {
        $userId = auth()->id();

        $order = Order::with([
            'items.productVariant.product',
            'items.size',
            'items.color'
        ])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json(['order' => $order]);
    }

   public function store(Request $request)
{
    if (!auth()->check()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'name' => 'required|string',
        'phone' => 'required|string',
        'address' => 'required|string',
        'items' => 'required|array',
        'items.*.product_variant_id' => 'required|exists:product_variants,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric',
        'items.*.size_id' => 'nullable|integer',
        'items.*.color_id' => 'nullable|integer',
    ]);

    DB::beginTransaction();
    try {
        $customer = auth()->user();
        $subtotal = collect($request->items)->sum(fn($i) => $i['price'] * $i['quantity']);
        $tax = 0;
        $shipping = 0;
        $total = $subtotal + $tax + $shipping;

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'status' => 'pending',
            'payment_method' => $request->payment_method ?? 'cod',
            'payment_status' => 'pending',
            'shipping_address' => $request->address,
            'billing_address' => $request->address,
            'customer_email' => $customer->email,
            'customer_phone' => $request->phone,
            'notes' => $request->notes ?? null,
        ]);

        foreach ($request->items as $item) {
            $order->items()->create([
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'size_id' => $item['size_id'] ?? null,
                'color_id' => $item['color_id'] ?? null,
            ]);
        }

        DB::commit();
        return response()->json(['message' => 'Đặt hàng thành công!'], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Lỗi khi đặt hàng',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}

}
