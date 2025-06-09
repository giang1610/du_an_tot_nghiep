@extends('layouts.admin')

@section('content')
<h1 class="text-xl font-bold mb-4">Chi tiết đơn hàng #{{ $order->id }}</h1>

<p><strong>Khách hàng:</strong> {{ $order->user->name ?? 'Khách vãng lai' }}</p>
<p><strong>Email:</strong> {{ $order->customer_email }}</p>
<p><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
<p><strong>Địa chỉ giao hàng:</strong> {{ $order->shipping_address }}</p>
<p><strong>Ghi chú:</strong> {{ $order->notes }}</p>

<h2 class="mt-4 font-semibold">Sản phẩm:</h2>
<table class="w-full table-auto">
    <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Phân loại</th>
            <th>Giá</th>
            <th>Số lượng</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->items as $item)
        <tr>
            <td>{{ $item->variant->product->name }}</td>
            <td>{{ $item->variant->color->name ?? '' }} / {{ $item->variant->size->name ?? '' }}</td>
            <td>{{ number_format($item->sale_price ?? $item->price, 0, ',', '.') }}đ</td>
            <td>{{ $item->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h3 class="mt-4">Tổng tiền: <strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></h3>
@endsection
