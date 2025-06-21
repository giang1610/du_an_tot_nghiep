{{-- filepath: resources/views/emails/orders/giao.blade.php --}}
@component('mail::message')
# Xin chào: {{ $order->user->name ?? 'Quý khách' }}

Đơn hàng của bạn gồm:
@foreach($order->items as $item)
- {{ $item->product->name ?? '' }}
@endforeach

Đơn hàng của bạn đã được giao, bạn hãy truy cập vào trang web để xác minh đã nhận hàng, bình luận và đánh giá sản phẩm.

Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!
@endcomponent