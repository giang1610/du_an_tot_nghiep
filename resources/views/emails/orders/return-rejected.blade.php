{{-- filepath: x:\laragon\www\du_an_tot_nghiep\resources\views\emails\orders\return-rejected.blade.php --}}
@component('mail::message')
# Yêu cầu hoàn hàng của bạn đã bị từ chối

Xin chào {{ $order->customer_name ?? 'Quý khách' }},

Yêu cầu hoàn hàng cho đơn hàng **#{{ $order->order_number }}** đã bị từ chối.

**Ghi chú từ admin:**
{{ $order->note_admin ?? 'Không có' }}

Nếu có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ.

Cảm ơn bạn đã sử dụng dịch vụ!
@endcomponent
