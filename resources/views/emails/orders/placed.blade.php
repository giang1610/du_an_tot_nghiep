@component('mail::message')
# Cảm ơn bạn đã đặt hàng!

**Mã đơn hàng:** {{ $order->order_number }}  
**Tổng tiền:** {{ number_format($order->total, 0, ',', '.') }} VND  
**Phương thức thanh toán:** {{ strtoupper($order->payment_method) === 'COD' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản' }}

@component('mail::panel')
**Thông tin đơn hàng:**
- Ngày đặt: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
- Địa chỉ giao hàng: {{ $order->shipping_address }}
- Số điện thoại: {{ $order->customer_phone }}
- Ghi chú: {{ $order->notes ?? 'Không có ghi chú' }}
@endcomponent

@component('mail::table')
| Sản phẩm            | Màu sắc | Kích thước | Số lượng | Giá gốc   | Giá khuyến mãi | Thành tiền |
|---------------------|---------|------------|----------|-----------|----------------|------------|
@foreach ($order->items as $item)
@php
    $isOnSale = $item->sale_price && now()->between(
        \Carbon\Carbon::parse($item->variant->sale_start_date),
        \Carbon\Carbon::parse($item->variant->sale_end_date)
    );
    $finalPrice = $isOnSale ? $item->sale_price : $item->variant->price;
    $rowTotal = $finalPrice * $item->quantity;
@endphp
| {{ $item->variant->product->name }} 
| {{ $item->variant->color->name }} 
| {{ $item->variant->size->name }} 
| {{ $item->quantity }} 
| {{ number_format($item->variant->price, 0, ',', '.') }} 
| {{ $isOnSale ? number_format($item->sale_price, 0, ',', '.') : '---' }} 
| {{ number_format($rowTotal, 0, ',', '.') }} |
@endforeach
@endcomponent

@component('mail::panel')
**Chi tiết thanh toán:**
- Tổng giá gốc: {{ number_format($order->subtotal + $order->discount, 0, ',', '.') }} VND
@if($order->discount > 0)
- Khuyến mãi: -{{ number_format($order->discount, 0, ',', '.') }} VND
@endif
- Phí vận chuyển: {{ number_format($order->shipping, 0, ',', '.') }} VND
- Thuế: {{ number_format($order->tax, 0, ',', '.') }} VND
- **Tổng cộng:** {{ number_format($order->total, 0, ',', '.') }} VND
@endcomponent

Cảm ơn bạn đã tin tưởng mua sắm cùng chúng tôi!

@component('mail::button', ['url' => '#'])
Xem đơn hàng
@endcomponent

Trân trọng,  
**MG Fashion Store**
@endcomponent