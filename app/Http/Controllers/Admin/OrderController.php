<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderGiao;
use App\Mail\OrderErrors;
use App\Mail\OrderPicking;
use App\Mail\OrderProcessing;
use App\Mail\OrderShipped;

class OrderController extends Controller
{
    // Danh sách đơn hàng theo trạng thái
    public function cancelled(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'cancelled', 'admin.orders.cancelled');
    }

    public function pending(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'pending', 'admin.orders.pending');
    }

    public function processing(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'processing', 'admin.orders.processing');
    }

    public function picking(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'picking', 'admin.orders.picking');
    }

    public function shipping(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'shipping', 'admin.orders.shipping');
    }

    public function shipped(Request $request)
    {
        return $this->filterOrdersByStatus($request, 'shipped', 'admin.orders.shipped');
    }

    // Hàm dùng chung lọc theo trạng thái
    protected function filterOrdersByStatus(Request $request, $status, $view)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view($view, compact('orders'));
    }

    // Sửa đơn hàng
    public function edit($id)
    {
        $order = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;

        $order->update($request->only([
            'status', 'customer_name', 'customer_phone', 'customer_address'
        ]));

        // Nếu trạng thái thay đổi thì gửi email tương ứng
        if ($order->status !== $oldStatus && $order->user && $order->user->email) {
            switch ($order->status) {
                case 'shipping':
                    Mail::to($order->user->email)->send(new OrderGiao($order));
                    break;
                case 'cancelled':
                    Mail::to($order->user->email)->send(new OrderErrors($order));
                    break;
                case 'picking':
                    Mail::to($order->user->email)->send(new OrderPicking($order));
                    break;
                case 'processing':
                    Mail::to($order->user->email)->send(new OrderProcessing($order));
                    break;
                case 'shipped':
                    Mail::to($order->user->email)->send(new OrderShipped($order));
                    break;
            }
        }

        return redirect()->route('admin.orders.edit', $order->id)
            ->with('success', 'Cập nhật đơn hàng thành công.');
    }
}
