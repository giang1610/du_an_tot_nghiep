{{-- filepath: resources/views/emails/orders/giao.blade.php --}}
@component('mail::message')
# Xin chào: {{ $order->user->name ?? 'Quý khách' }}

Đơn hàng của bạn gồm:
@foreach($order->items as $item)
- {{ $item->product->name ?? '' }}
@endforeach

Đơn hàng của bạn đang được lấy hàng.

Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!
@endcomponent