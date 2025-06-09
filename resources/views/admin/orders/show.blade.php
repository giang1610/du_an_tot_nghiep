@extends('layouts.admin') {{-- hoặc layouts.app nếu chưa có layout admin --}}

@section('content')
<div class="container mt-4">
    <h2>Chi tiết đơn hàng: {{ $order->order_number ?? 'N/A' }}</h2>

    <hr>

    <h4>Thông tin khách hàng</h4>
    <ul>
        <li><strong>Email:</strong> {{ $order->customer_email }}</li>
        <li><strong>Điện thoại:</strong> {{ $order->customer_phone }}</li>
        <li><strong>Địa chỉ giao hàng:</strong> {{ $order->shipping_address }}</li>
        <li><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</li>
        <li><strong>Ghi chú:</strong> {{ $order->notes }}</li>
        <li><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</li>
    </ul>

    <h4>Sản phẩm</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Hình ảnh</th>
                <th>Phân loại</th>
                <th>Số lượng</th>
                <th>Giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->variant->product->name }}</td>
                    <td>
                        @if($item->variant->product->thumbnail)
                            <img src="{{ asset('storage/' . $item->variant->product->thumbnail) }}" width="60">
                        @endif
                    </td>
                    <td>
                        Màu: <img src="{{ asset('storage/' . $item->variant->color->value) }}" width="20">
                        | Size: {{ $item->variant->size->name }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price) }}₫</td>
                    <td>{{ number_format($item->price * $item->quantity) }}₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Tổng kết</h4>
    <ul>
        <li><strong>Tạm tính:</strong> {{ number_format($order->subtotal) }}₫</li>
        <li><strong>Thuế:</strong> {{ number_format($order->tax) }}₫</li>
        <li><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping) }}₫</li>
        <li><strong>Tổng thanh toán:</strong> {{ number_format($order->total) }}₫</li>
    </ul>
</div>
@endsection
