@component('mail::message')
# 🎉 Cảm ơn bạn đã tham gia {{ config('app.name') }}


Cảm ơn bạn đã đăng ký tài khoản tại **{{ config('app.name') }}**.  
Để hoàn tất quá trình đăng ký và đảm bảo bạn là chủ sở hữu của địa chỉ email này, vui lòng xác minh email của bạn bằng cách nhấn vào nút bên dưới:

@component('mail::button', ['url' => $verificationUrl])
🔒 Xác minh Email
@endcomponent

> Nếu bạn không thực hiện yêu cầu này, bạn có thể bỏ qua email này. Không có hành động nào sẽ được thực hiện.

Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi!  
Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với đội ngũ hỗ trợ.

Trân trọng,  
**{{ config('app.name') }} Team**
@endcomponent
