<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
</head>
<body>
    <h2>Đăng nhập</h2>

    @if (session('status'))
        <div style="color: green">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mật khẩu:</label><br>
        <input type="password" name="password" required><br><br>

        <label>
            <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
        </label><br><br>

        <button type="submit">Đăng nhập</button>
    </form>

    <br>
    <a href="{{ route('register') }}">Chưa có tài khoản? Đăng ký</a><br>
    <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
</body>
</html>
