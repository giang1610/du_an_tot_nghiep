@component('mail::message')
# 🔐 Khôi phục mật khẩu

Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.

@component('mail::button', ['url' => $resetUrl])
Đặt lại mật khẩu
@endcomponent

Nếu bạn không yêu cầu, vui lòng bỏ qua email này.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
