@extends('admin.layouts.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<h2>Danh sách đơn hàng</h2>
<div class="container mt-4">
    
    <form method="GET" action="{{ route('orders.index') }}" class="row g-3 mb-4 align-items-center">

    <div class="col-auto">
        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." 
            value="{{ request('search') }}">
    </div>

    <div class="col-auto">
        <select name="status" class="form-select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Đã hủy</option>
            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Chờ xử lý</option>
            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Đang xử lý</option>
            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Đang lấy hàng</option>
            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Đang giao hàng</option>
            <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Đã giao hàng</option>
            <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>Xác minh nhận hàng</option>
            {{-- <option value="0" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            <option value="1" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
            <option value="2" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
            <option value="3" {{ request('status') == 'picking' ? 'selected' : '' }}>Đang lấy hàng</option>
            <option value="4" {{ request('status') == 'shipping' ? 'selected' : '' }}>Đang giao hàng</option>
            <option value="5" {{ request('status') == 'shipped' ? 'selected' : '' }}>Đã giao hàng</option>
            <option value="6" {{ request('status') == 'completed' ? 'selected' : '' }}>Xác minh nhận hàng</option> --}}
        </select>
    </div>

    <div class="col-auto">
        <label for="from_date" class="form-label mb-0">Từ ngày:</label>
        <input type="date" name="from_date" id="from_date" class="form-control" 
            value="{{ request('from_date') }}">
    </div>

    <div class="col-auto">
        <label for="to_date" class="form-label mb-0">Đến ngày:</label>
        <input type="date" name="to_date" id="to_date" class="form-control" 
            value="{{ request('to_date') }}">
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Lọc</button>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Xóa lọc</a>
    </div>

</form>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Sản phẩm đặt</th> 
                <th>Số điện thoại</th> 
                <th>Ngày tạo</th>
                <th>Tổng tiền</th>
                <th>Trạng thái thanh toán</th>
                <th>Trạng thái đơn hàng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
              @php
                    $variant = $order->variant;
                    $product = $variant->product ?? null;
                    // $thumbnail = $product?->thumbnail;
                    $thumbnail = $variant->image ?? ($product?->image ?? null);
                    $price = $order->price;
                    $salePrice = $order->sale_price ?? $price;
                    $totalPrice = $salePrice * $order->quantity;
                    $hasDiscount = $salePrice < $price;
                @endphp
            <tr>
                <td>{{ $order->order_number ?? 'ORD-' . $order->id }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>{{ $order->$product }}</td> 
                <td>{{ $order->customer_phone }}</td> 
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ number_format($order->total) }}₫</td>
                <td>{{ $order->payment_status }}</td> 
                <td>
                    @switch($order->status)
                    @case('1')
                    <span class="badge bg-warning">Chờ xử lý</span>
                    @break
                    @case('2')
                    <span class="badge bg-primary">Đang xử lý</span>
                    @break
                    @case('3')
                    <span class="badge bg-info">Đang lấy hàng</span>
                    @break
                    @case('4')
                    <span class="badge bg-secondary">Đang giao hàng</span>
                    @break
                    @case('5')
                    <span class="badge bg-success">Đã giao hàng</span>
                    @break
                    @case('6')
                    <span class="badge bg-success">Xác minh nhận hàng</span>
                    @break
                    @case('0')
                    <span class="badge bg-danger">Đã hủy</span>
                    @break
                    @default
                    <span class="badge bg-secondary">Không rõ</span>
                    @endswitch
                </td>

                
                <td>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem</a>
                    {{-- <a href="" class="btn btn-sm btn-toolbar">In đơn</a> --}}
                    @if (!in_array($order->status, ['0', '6']))
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-success">Sửa</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Không có đơn hàng nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Hiển thị phân trang --}}
    {{ $orders->links('pagination::bootstrap-5') }}
</div>
@endsection