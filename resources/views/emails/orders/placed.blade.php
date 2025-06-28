@component('mail::message')
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
