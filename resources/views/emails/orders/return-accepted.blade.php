{{-- filepath: x:\laragon\www\du_an_tot_nghiep\resources\views\emails\orders\return-accepted.blade.php --}}
@component('mail::message')
# Yêu cầu hoàn hàng của bạn đã được chấp nhận

Xin chào {{ $order->customer_name ?? 'Quý khách' }},

Yêu cầu hoàn hàng cho đơn hàng **#{{ $order->order_number }}** đã được chấp nhận.

**Ghi chú từ admin:**
{{ $order->note_admin ?? 'Không có' }}

Chúng tôi sẽ liên hệ để hướng dẫn bạn quy trình hoàn hàng.

Cảm ơn bạn đã sử dụng dịch vụ!
@endcomponent
