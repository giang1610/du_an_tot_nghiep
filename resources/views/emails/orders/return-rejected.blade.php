{{-- filepath: x:\laragon\www\du_an_tot_nghiep\resources\views\emails\orders\return-rejected.blade.php --}}
{{-- @component('mail::message')
# Yêu cầu hoàn hàng của bạn đã bị từ chối

Xin chào {{ $order->user->name ?? 'Quý khách' }},

Yêu cầu hoàn hàng cho đơn hàng **#{{ $order->order_number }}** đã bị từ chối.

**Ghi chú từ admin:**
{{ $order->note_admin ?? 'Không có' }}

Nếu có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ.

Cảm ơn bạn đã sử dụng dịch vụ!
@endcomponent --}}
@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo.png') }}" alt="MG Fashion Store" style="max-height: 80px;">
    <h1 style="color: #2d3748; margin-top: 10px;">Yêu cầu hoàn hàng của bạn đã bị từ chối!</h1>
</div>

<div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h2 style="color: #2d3748; margin-top: 0;">Thông tin đơn hàng</h2>
    <p><strong>Mã đơn hàng:</strong> {{ $order->order_number }}</p>
    <p><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
    <p><strong>Tổng tiền:</strong> <span style="color: #e53e3e; font-weight: bold;">{{ number_format($order->total, 0, ',', '.') }} VND</span></p>
    <p><strong>Phương thức thanh toán:</strong> {{ strtoupper($order->payment_method) === 'COD' ? 'Thanh toán khi nhận hàng' : 'Momo' }}</p>
</div>

<div style="margin-bottom: 20px;">
    <h3 style="color: #2d3748; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Thông tin giao hàng</h3>
    <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
    <p><strong>Số điện thoại:</strong> {{ $order->customer_phone }}</p>
    <p><strong>Ghi chú:</strong> {{ $order->notes ?? 'Không có ghi chú' }}</p>
</div>

<div style="margin-bottom: 20px;">
    <h3 style="color: #2d3748; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Chi tiết đơn hàng</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #edf2f7;">
                <th style="padding: 12px; text-align: left;">Sản phẩm</th>
                <th style="padding: 12px; text-align: center;">Màu sắc</th>
                <th style="padding: 12px; text-align: center;">Kích thước</th>
                <th style="padding: 12px; text-align: center;">Số lượng</th>
                <th style="padding: 12px; text-align: right;">Đơn giá</th>
                <th style="padding: 12px; text-align: right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            @php
                $isOnSale = $item->sale_price && now()->between(
                    \Carbon\Carbon::parse($item->variant->sale_start_date),
                    \Carbon\Carbon::parse($item->variant->sale_end_date)
                );
                $finalPrice = $isOnSale ? $item->sale_price : $item->variant->price;
                $rowTotal = $finalPrice * $item->quantity;
            @endphp
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 12px;">{{ $item->variant->product->name }}</td>
                <td style="padding: 12px; text-align: center;">{{ $item->variant->color->name }}</td>
                <td style="padding: 12px; text-align: center;">{{ $item->variant->size->name }}</td>
                <td style="padding: 12px; text-align: center;">{{ $item->quantity }}</td>
                <td style="padding: 12px; text-align: right;">
                    @if($isOnSale)
                        <span style="text-decoration: line-through; color: #a0aec0;">{{ number_format($item->variant->price, 0, ',', '.') }}</span>
                        <br>
                        <span style="color: #e53e3e;">{{ number_format($item->sale_price, 0, ',', '.') }}</span>
                    @else
                        {{ number_format($item->variant->price, 0, ',', '.') }}
                    @endif
                </td>
                <td style="padding: 12px; text-align: right;">{{ number_format($rowTotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
    <h3 style="color: #2d3748; margin-top: 0;">Tổng thanh toán</h3>
    <table style="width: 100%;">
        <tr>
            <td style="padding: 8px 0;">Tổng giá gốc:</td>
            <td style="text-align: right; padding: 8px 0;">{{ number_format($order->subtotal + $order->discount, 0, ',', '.') }} VND</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td style="padding: 8px 0;">Khuyến mãi:</td>
            <td style="text-align: right; padding: 8px 0; color: #e53e3e;">-{{ number_format($order->discount, 0, ',', '.') }} VND</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 8px 0;">Phí vận chuyển:</td>
            <td style="text-align: right; padding: 8px 0;">{{ number_format($order->shipping, 0, ',', '.') }} VND</td>
        </tr>
        <tr>
            <td style="padding: 8px 0;">Thuế:</td>
            <td style="text-align: right; padding: 8px 0;">{{ number_format($order->tax, 0, ',', '.') }} VND</td>
        </tr>
        <tr style="font-weight: bold; border-top: 1px solid #e2e8f0;">
            <td style="padding: 12px 0;">Tổng cộng:</td>
            <td style="text-align: right; padding: 12px 0; color: #e53e3e;">{{ number_format($order->total, 0, ',', '.') }} VND</td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin-bottom: 30px;">
    <p style="font-size: 16px; color: #4a5568;">Cảm ơn bạn đã tin tưởng mua sắm tại MG Fashion Store!</p>
    <p style="font-size: 14px; color: #718096;">Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>
</div>

<div style="text-align: center; color: #718096; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
    <p>Nếu có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email: support@mgfashion.com</p>
    <p>MG Fashion Store - Thời trang cho mọi người</p>
</div>
@endcomponent
