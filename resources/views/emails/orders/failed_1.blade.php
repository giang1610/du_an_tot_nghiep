{{-- filepath: resources/views/emails/orders/giao.blade.php --}}
@component('mail::message')
# Xin chào: {{ $order->user->name ?? 'Quý khách' }}

Đơn hàng của bạn gồm:
@foreach($order->items as $item)
- {{ $item->product->name ?? '' }}
@endforeach

Đơn hàng của bạn đã giao thất bại sau 1 lần. Bạn chú ý nhận hàng lại hoặc liên hệ với chúng tôi để được hỗ trợ.

Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!
@endcomponent
