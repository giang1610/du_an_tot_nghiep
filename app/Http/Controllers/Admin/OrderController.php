<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        // Lấy danh sách đơn hàng mới nhất, phân trang
        $orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // hoặc ->get() nếu không muốn phân trang

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('items.variant.product', 'items.variant.color', 'items.variant.size')->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }
}

