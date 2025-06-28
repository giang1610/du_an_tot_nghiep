<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Events\OrderStatusUpdated; // Import sự kiện OrderStatusUpdated
use App\Mail\OrderGiao; // Import mail OrderGiao
use App\Mail\OrderErrors; // Import mail OrderErrors
use App\Mail\OrderPicking;
use App\Mail\OrderProcessing;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail; // Import facade Mail

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');

        // Lọc theo status nếu có
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo ngày bắt đầu (created_at >= from_date)
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        // Lọc theo ngày kết thúc (created_at <= to_date)
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
            'items.variant.product',    // tên sản phẩm
            'items.variant.color',      // màu sắc
            'items.variant.size'        // size
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }
    public function updateStatus(Request $request, $id)
    {
        // $order = Order::findOrFail($id);
        // $this->authorize('update', $order); // Kiểm tra quyền cập nhật

        // $request->validate([
        //     'status' => 'required|in:pending,processing,completed,cancelled',
        // ]);

        // $order->status = $request->status;
        // $order->save();

        // // Phát sự kiện cập nhật trạng thái đơn hàng
        // broadcast(new \App\Events\OrderStatusUpdated($order->id, $order->status))->toOthers();

        // return redirect()->route('admin.orders.show', $order->id)->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
        $order = Order::findOrFail($id);
        $order->status = $request->input('status');
        $order->save();

        // Gửi sự kiện WebSocket tới client
        broadcast(new OrderStatusUpdated($order->id, $order->status))->toOthers();

        return response()->json(['message' => 'Cập nhật trạng thái đơn hàng thành công']);
    }
    public function edit(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.edit', compact('order'));
    }
    public function update(Request $request, $id)
    {
       $order = Order::findOrFail($id);
    $oldStatus = $order->status;
    $order->update($request->all());

    // Nếu trạng thái thay đổi và là "shipping" thì gửi mail
    if ($order->status !== $oldStatus && $order->status === 'shipping') {
        Mail::to($order->user->email)->send(new OrderGiao($order));
    }
    if ($order->status !== $oldStatus && $order->status === 'cancelled') {
        Mail::to($order->user->email)->send(new OrderErrors($order));
    }
    if ($order->status !== $oldStatus && $order->status === 'picking') {
        Mail::to($order->user->email)->send(new OrderPicking($order));
    }
    if ($order->status !== $oldStatus && $order->status === 'processing') {
        Mail::to($order->user->email)->send(new OrderProcessing($order));
    }
    if ($order->status !== $oldStatus && $order->status === 'shipped') {
        Mail::to($order->user->email)->send(new OrderShipped($order));
    }
        return redirect()->route('orders.index', $order->id)->with('success', 'Cập nhật đơn hàng thành công.');
    }
}
