<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hủy đơn hàng</title>
</head>
<body>
    <h2>Xin chào {{ $order->customer_name }},</h2>

    <p>Đơn hàng #{{ $order->id }} của bạn đã bị hủy bởi quản trị viên.</p>

    <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.</p>

    <p>Trân trọng,<br>
    Đội ngũ hỗ trợ</p>
</body>
</html>
