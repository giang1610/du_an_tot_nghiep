@component('mail::message')


Bạn đã Tham gia WEB vui lòng xác minh Email để sau mua hàng :

@component('mail::button', ['url' => $verificationUrl])
Xác minh Email 
@endcomponent

Nếu bạn không yêu cầu, vui lòng bỏ qua email này.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
