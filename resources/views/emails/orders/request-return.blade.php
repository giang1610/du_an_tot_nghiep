{{-- filepath: x:\laragon\www\du_an_tot_nghiep\resources\views\emails\orders\request-return.blade.php --}}
@component('mail::message')
# Yêu cầu hoàn hàng mới

Khách hàng: {{ $order->customer_email }}
Mã đơn hàng: #{{ $order->order_number }}

**Lý do hoàn hàng:**
{{ $order->return_reason }}

@component('mail::button', ['url' => url('/admin/orders/'.$order->id)])
Xem đơn hàng
@endcomponent

@endcomponent
