<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Events\OrderStatusUpdated; // Import sự kiện OrderStatusUpdated
use App\Mail\OrderGiao;
use App\Mail\OrderErrors;
use App\Mail\OrderPicking;
use App\Mail\OrderProcessing;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;
use App\Jobs\UpdateOrderStatus;
use App\Mail\OrderCancelledMail;
use App\Models\Stock;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->orderBy('id', 'desc');

        // Lọc theo status nếu có
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%") // 👈 lọc đúng theo mã đơn
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

        $orders = $query->paginate(15)->withQueryString(); // Trả về danh sách đơn hàng với 10 bản ghi/trang


        return view('admin.orders.index', compact('orders'));
    }
    public function cancelled(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'cancelled')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.cancelled', compact('orders'));
    }

    public function pending(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.pending', compact('orders'));
    }

    public function processing(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'processing')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.processing', compact('orders'));
    }

    public function picking(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'picking')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.picking', compact('orders'));
    }

    public function shipping(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'shipping')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.shipping', compact('orders'));
    }

    public function shipped(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'shipped')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.shipped', compact('orders'));
    }

     public function completed(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.completed', compact('orders'));
    }

    public function failed(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.failed', compact('orders'));
    }

    public function returning(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'returning')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.returning', compact('orders'));
    }

    public function return_requested(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'return_requested')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.return_requested', compact('orders'));
    }

        public function returned(Request $request)
    {
        $query = Order::with(['user', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->where('status', 'returned')
            ->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa (mã đơn hàng, tên user, email, số điện thoại khách hàng)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%") // Thêm tìm kiếm theo số điện thoại
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

        return view('admin.orders.returned', compact('orders'));
    }

    /**
     * Cập nhật thông tin khách hàng (số điện thoại và địa chỉ giao hàng)
     */
    public function changePhoneAddress(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([ 'customer_phone' => [
            'required',
            'string',
            'regex:/^0[0-9]+$/'
        ],
            'shipping_address' => 'required|string|min:5|max:255',
        ],
            [
                'customer_phone.required' => 'Số điện thoại không được để trống.',
                
                'customer_phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0, 10 kí tự và chỉ chứa các chữ số.',
                
                'shipping_address.required' => 'Địa chỉ giao hàng không được để trống.',
                'shipping_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự.',
                'shipping_address.min' => 'Địa chỉ giao hàng phải ít nhất 5 ký tự.',
            ]
    );

        $order->customer_phone = $validated['customer_phone'];
        $order->shipping_address = $validated['shipping_address'];
        $order->save();
        // Gửi email thông báo cập nhật thông tin khách hàng
            
        if ($order->customer_email) {
            \Mail::to($order->customer_email)->send(new \App\Mail\OrderCustomerInfoChanged($order));
        }
        
        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }

    public function show(Request $request, $id)
    {
        $order = Order::with([
            'items.variant.product',    // tên sản phẩm
            'items.variant.color',      // màu sắc
            'items.variant.size'        // size
        ])->findOrFail($id);
        $order = Order::findOrFail($id);
    $oldStatus = $order->status;

    // Cập nhật thông tin khách hàng nếu có
    if ($request->has('customer_phone')) {
        $order->customer_phone = $request->input('customer_phone');
    }
    if ($request->has('shipping_address')) {
        $order->shipping_address = $request->input('shipping_address');
    }

        return view('admin.orders.show', compact('order'));
    }
   
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->input('status');
        


        $order->status = $newStatus;

        // Nếu trạng thái là đã giao hàng / hoàn thành → đánh dấu đã thanh toán
        if (in_array($newStatus, ['shipped', 'completed']) && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }

        $order->save();


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
        UpdateOrderStatus::dispatch($order->id);

        

       // Nếu trạng thái giao hàng là "shipped" và chưa thanh toán thì tự động chuyển sang "paid"
        if ($order->status === 'shipped' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->shipped_at = now(); // Cập nhật thời gian giao hàng
            $order->save();

        }
        // Nếu trạng thái thay đổi từ "shipper_en_route" sang "restocked" thì cập nhật lại số lượng kho
        if ($oldStatus === 'shipper_en_route' && $order->status === 'restocked') {
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::where('product_variant_id', $item->product_variant_id)->first();
                if ($stock) {
                    $stock->quantity += $item->quantity;
                    $stock->save();
                }
            }
        }
        if ($oldStatus === 'failed' && $order->status === 'restocked') {
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::where('product_variant_id', $item->product_variant_id)->first();
                if ($stock) {
                    $stock->quantity += $item->quantity;
                    $stock->save();
                }
            }
        }

        // Nếu trạng thái thay đổi và là "cancelled" thì cộng lại số lượng vào kho của từng variant
            if ($order->status === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    // Tìm bản ghi stock của variant
                    $stock = \App\Models\Stock::where('product_variant_id', $item->product_variant_id)->first();
                    if ($stock) {
                        $stock->quantity += $item->quantity;
                        $stock->save();
                    }
                }
            }

        if ($order->status !== $oldStatus) {
        \App\Jobs\UpdateOrderStatus::dispatch($order->id);
    }
        return redirect()->route('orders.index', $order->id)->with('success', 'Cập nhật đơn hàng thành công.');
    }

    public function handleReturn(Request $request, $id)
{
    $order = Order::findOrFail($id);

    $request->validate([
        'action' => 'required|in:accept,reject',
        'note_admin' => 'nullable|string',
    ]);

    $order->note_admin = $request->input('note_admin');

    if ($request->action === 'accept') {
        $order->status = 'returned';
        $order->returned_at = now();
        $order->save();
        Mail::to($order->customer_email)->queue(new \App\Mail\ReturnAccepted($order));
    } else {
        $order->status = 'completed';
        $order->completed_at = now();
        $order->save();
        Mail::to($order->customer_email)->queue(new \App\Mail\ReturnRejected($order));
    }

    return redirect()->route('orders.index', $order->id)
        ->with('success', 'Đã xử lý yêu cầu hoàn hàng.');
}

    public function destroy($id)
{
    $order = Order::findOrFail($id);

    // kiểm tra điều kiện xóa
    if ($order->status !== 'completed' || !$order->completed_at || $order->completed_at->addDays(7)->isFuture()) {
        return redirect()->route('orders.index')
            ->with('error', 'Không thể xóa: đơn hàng phải ở trạng thái hoàn thành và đã quá 7 ngày kể từ khi hoàn thành.');
    }

    // (tuỳ: nếu dùng soft delete thì giữ như này, nếu muốn xóa cứng thì ->forceDelete())
    $order->delete();

    return redirect()->route('orders.index')
        ->with('success', 'Đơn hàng đã được xóa thành công.');
}


}


