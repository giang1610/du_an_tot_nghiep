@component('mail::message')
# Xác minh email mới

Bạn đã yêu cầu cập nhật email. Vui lòng nhấn vào nút bên dưới để xác minh địa chỉ email mới:

@component('mail::button', ['url' => $verificationUrl])
Xác minh Email Mới
@endcomponent

Nếu bạn không yêu cầu, vui lòng bỏ qua email này.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
