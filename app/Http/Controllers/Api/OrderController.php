<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
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
