@component('mail::message')
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 7a55765037e668cddf5d993c421aca690eda95b5
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo.png') }}" alt="MG Fashion Store" style="max-height: 80px;">
    <h1 style="color: #2d3748; margin-top: 10px;">Cảm ơn bạn đã đặt hàng!</h1>
</div>

<div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h2 style="color: #2d3748; margin-top: 0;">Thông tin đơn hàng</h2>
    <p><strong>Mã đơn hàng:</strong> {{ $order->order_number }}</p>
    <p><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
    <p><strong>Tổng tiền:</strong> <span style="color: #e53e3e; font-weight: bold;">{{ number_format($order->total, 0, ',', '.') }} VND</span></p>
    <p><strong>Phương thức thanh toán:</strong> {{ strtoupper($order->payment_method) === 'COD' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản' }}</p>
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
<<<<<<< HEAD
=======
# 🎉 Cảm ơn bạn đã đặt hàng tại **MG Fashion Store**!

**Mã đơn hàng:** `{{ $order->order_number }}`  
**Tổng tiền:** **{{ number_format($order->total, 0, ',', '.') }}₫**  
**Phương thức thanh toán:** {{ strtoupper($order->payment_method) === 'COD' ? '💵 Thanh toán khi nhận hàng' : '🏦 Chuyển khoản ngân hàng' }}

---

@component('mail::panel')
### 📝 Thông tin đơn hàng
- 🕒 Ngày đặt: **{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}**
- 📍 Địa chỉ giao hàng: **{{ $order->shipping_address }}**
- 📞 Số điện thoại: **{{ $order->customer_phone }}**
- 🗒 Ghi chú: {{ $order->notes ?? 'Không có ghi chú' }}
@endcomponent

---

@component('mail::table')
| Sản phẩm | Màu sắc | Kích cỡ | SL | Giá gốc | KM | Thành tiền |
|:--------:|:-------:|:------:|:--:|--------:|----:|-----------:|
@foreach ($order->items as $item)
@php
    $variant = optional($item->productVariant);
    $product = optional($variant->product);
    $color = optional($variant->color);
    $size = optional($variant->size);
    $isOnSale = $item->sale_price && now()->between(
        \Carbon\Carbon::parse($variant->sale_start_date),
        \Carbon\Carbon::parse($variant->sale_end_date)
    );
    $finalPrice = $isOnSale ? $item->sale_price : $variant->price;
    $rowTotal = $finalPrice * $item->quantity;
@endphp
| {{ $product->name ?? '---' }} 
| {{ $color->name ?? '---' }} 
| {{ $size->name ?? '---' }} 
| {{ $item->quantity }} 
| {{ number_format($variant->price ?? 0, 0, ',', '.') }}₫ 
| {{ $isOnSale ? number_format($item->sale_price, 0, ',', '.') . '₫' : '---' }} 
| **{{ number_format($rowTotal, 0, ',', '.') }}₫** |
@endforeach
@endcomponent

---

@component('mail::panel')
### 💰 Chi tiết thanh toán
- Tổng giá sản phẩm: {{ number_format($order->subtotal + ($order->discount ?? 0), 0, ',', '.') }}₫  
@if($order->discount > 0)
- Giảm giá: <span style="color:red;">-{{ number_format($order->discount, 0, ',', '.') }}₫</span>  
@endif
- Phí vận chuyển: {{ number_format($order->shipping, 0, ',', '.') }}₫  
- Thuế (VAT): {{ number_format($order->tax, 0, ',', '.') }}₫  
- 👉 **Tổng cộng cần thanh toán: {{ number_format($order->total, 0, ',', '.') }}₫**
@endcomponent

---

@component('mail::button', ['url' => url('/my-orders')])
🛍 Xem đơn hàng của bạn
@endcomponent

Một lần nữa, cảm ơn bạn đã tin tưởng mua sắm cùng **MG Fashion Store**.  
Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi.

Trân trọng,  
**MG Fashion Store**
@endcomponent
>>>>>>> d05ea496963963675dbd1e8fecebf3d54c40d8dd
=======
>>>>>>> 7a55765037e668cddf5d993c421aca690eda95b5
