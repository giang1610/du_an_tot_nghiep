@extends('admin.layouts.app')

@section('content')
  <div class="d-flex align-items-center mb-4">
        <a href="/admin/orders" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách đơn hàng</span>
        </a>
    </div>
<div class="container mt-4">
    <h2>
        Chi tiết đơn hàng: {{ $order->order_number }}
        <span class="badge bg-info text-dark">
        {{
            match($order->status) {
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'picking' => 'Đang lấy hàng',
                'shipping' => 'Đang giao hàng',
                'shipped' => 'Đã giao hàng',
                'completed' => 'Xác minh nhận hàng',
                'cancelled' => 'Đã hủy',
                default => ucfirst($order->status)
            }
        }}
    </span>

    </h2>

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

    <h4>Sản phẩm đã đặt</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Ảnh</th>
                <th>Phân loại</th>
                <th>Số lượng</th>
                <th>Giá gốc</th>
                <th>Giá khuyến mãi</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            @php
            $variant = $item->variant;
            $product = $variant->product ?? null;
            // $thumbnail = $product?->thumbnail;
            $thumbnail = $variant->image ?? ($product?->image ?? null);
            $price = $item->price;
            $salePrice = $item->sale_price ?? $price;
            $totalPrice = $salePrice * $item->quantity;
            $hasDiscount = $salePrice < $price;
                @endphp
                <tr>
                <td>{{ $product->name ?? 'Không rõ' }}</td>
                <td>
                    @if($thumbnail)
                    <img src="{{ asset('storage/' . $thumbnail) }}" width="60">
                    @else
                    Không có ảnh
                    @endif
                </td>
                <td>
                    Màu:
                    @if($variant->color && $variant->color->image)
                    {{-- <img src="{{ asset('storage/' . $variant->color->image) }}" width="20"> --}}
                    ({{ $variant->color->name }})
                    @else
                    Không có
                    @endif
                    |
                    Size: {{ $variant->size->name ?? 'Không có' }}
                </td>
                <td>{{ $item->quantity }}</td>

                {{-- Giá gốc --}}
                <td>
                    @if($hasDiscount)
                    <del>{{ number_format($price) }}₫</del>
                    @else
                    {{ number_format($price) }}₫
                    @endif
                </td>

                {{-- Giá khuyến mãi --}}
                <td>
                    @if($hasDiscount)
                    <span class="text-danger">{{ number_format($salePrice) }}₫</span>
                    @else
                    -
                    @endif
                </td>

                <td>{{ number_format($totalPrice) }}₫</td>
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