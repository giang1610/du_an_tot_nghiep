<p>Xin chào {{ $user->name }},</p>
<p>Bạn đã được thêm vào hệ thống với vai trò nhân viên.</p>
<p>Thông tin đăng nhập:</p>
<ul>
    <li>Email: <strong>{{ $user->email }}</strong></li>
    <li>Mật khẩu: <strong>{{ $password }}</strong></li>
</ul>
<p>Vui lòng đăng nhập vào hệ thống.</p>
<p>Trân trọng,<br>Quản trị hệ thống</p>