@component('mail::message')

    <h2>Xin chào {{ $order->customer_name ?? 'khách hàng' }},</h2>

    <p>Đơn hàng <strong>#{{ $order->id }}</strong> của bạn đã được tạo lúc {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}, nhưng đến hiện tại vẫn chưa được thanh toán thành công.</p>

    <p>Do đó, hệ thống đã tự động hủy đơn hàng để tránh giữ tồn kho quá lâu.</p>

    <p>Nếu bạn vẫn muốn mua sản phẩm, vui lòng đặt hàng lại. Xin cảm ơn!</p>

    <hr>
    <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>

@endcomponent
