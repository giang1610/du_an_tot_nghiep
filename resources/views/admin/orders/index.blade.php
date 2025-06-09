@extends('admin.layouts.app')

@section('content')
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
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
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
                <!-- <th>Email</th> -->
                <th>Ngày tạo</th>
                <th>Trạng thái</th>
                <th>Tổng tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            <tr>
                <td>{{ $order->order_number ?? 'ORD-' . $order->id }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <!-- <td>{{ $order->customer_email }}</td> -->
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @switch($order->status)
                    @case('pending')
                    <span class="badge bg-warning">Chờ xử lý</span>
                    @break
                    @case('processing')
                    <span class="badge bg-primary">Đang xử lý</span>
                    @break
                    @case('completed')
                    <span class="badge bg-success">Hoàn thành</span>
                    @break
                    @case('cancelled')
                    <span class="badge bg-danger">Đã hủy</span>
                    @break
                    @default
                    <span class="badge bg-secondary">Không rõ</span>
                    @endswitch
                </td>
                <td>{{ number_format($order->total) }}₫</td>
                <td>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem</a>
                    <a href="" class="btn btn-sm btn-toolbar">In đơn</a>
                    <!-- <a href="" class="btn btn-sm btn-info">Sửa</a> -->
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