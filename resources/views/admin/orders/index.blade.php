@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2>Danh sách đơn hàng</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Email</th>
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
                    <td>{{ $order->customer_email }}</td>
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
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem</a>
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
    {{ $orders->links() }}
</div>
@endsection
