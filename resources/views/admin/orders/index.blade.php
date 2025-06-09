@extends('layouts.admin')

@section('content')
<h1 class="text-xl font-bold mb-4">Danh sách đơn hàng</h1>

<table class="w-full table-auto border-collapse">
    <thead>
        <tr>
            <th>Mã</th>
            <th>Khách hàng</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
        <tr class="border-b">
            <td>{{ $order->id }}</td>
            <td>{{ $order->user->name ?? 'Khách vãng lai' }}</td>
            <td>{{ number_format($order->total, 0, ',', '.') }}đ</td>
            <td>{{ $order->status ?? 'Chờ xử lý' }}</td>
            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            <td><a href="{{ route('admin.orders.show', $order) }}" class="text-blue-500">Xem</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $orders->links() }}
@endsection
