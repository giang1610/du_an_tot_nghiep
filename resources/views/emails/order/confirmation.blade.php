@component('mail::message')
# Cảm ơn bạn đã đặt hàng!

**Tên:** {{ $order->name }}  
**Địa chỉ:** {{ $order->address }}  
**Tổng tiền:** {{ number_format($order->total) }} đ

## Chi tiết đơn hàng:
@foreach ($order->items as $item)
- {{ $item->product_name }} (x{{ $item->quantity }}): {{ number_format($item->price * $item->quantity) }} đ
@endforeach

Chúng tôi sẽ xử lý đơn hàng và giao trong thời gian sớm nhất.

Cảm ơn bạn!  
{{ config('app.name') }}
@endcomponent
