<!-- resources/views/auth/verify-email.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Xác minh Email</title>
</head>
<body>
    <h2>Vui lòng xác minh email của bạn</h2>

    @if (session('status') == 'verification-link-sent')
        <div style="color: green;">
            Một link xác minh mới đã được gửi đến email của bạn.
        </div>
    @endif

    <p>Trước khi tiếp tục, vui lòng kiểm tra email của bạn để lấy link xác minh.</p>
    <p>Nếu bạn chưa nhận được email, vui lòng nhấn nút dưới đây để gửi lại.</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Gửi lại link xác minh</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Đăng xuất</button>
    </form>
</body>
</html>
