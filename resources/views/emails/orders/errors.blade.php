{{-- filepath: resources/views/emails/orders/giao.blade.php --}}
@component('mail::message')
# Xin chào: {{ $order->user->name ?? 'Quý khách' }}

Đơn hàng của bạn gồm:
@foreach($order->items as $item)
- {{ $item->product->name ?? '' }}
@endforeach

đã xảy ra lỗi quý khách vui lòng đặt lại hàng.

Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!
@endcomponent