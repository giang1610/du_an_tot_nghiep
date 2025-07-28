@component('mail::message')
# 📧 Xác minh Email Mới

Xin chào

Bạn (hoặc ai đó) vừa yêu cầu cập nhật địa chỉ email cho tài khoản tại **{{ config('app.name') }}**.

Để xác minh địa chỉ email mới của bạn, vui lòng nhấn vào nút bên dưới:

@component('mail::button', ['url' => $verificationUrl])
🔒 Xác Minh Email Mới
@endcomponent

> Nếu bạn không yêu cầu thay đổi này, vui lòng **bỏ qua** email này. Không có hành động nào sẽ được thực hiện.

Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi!

Trân trọng,  
**{{ config('app.name') }} Team**
@endcomponent
