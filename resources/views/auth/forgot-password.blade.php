<!-- resources/views/auth/forgot-password.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Quên mật khẩu</title>
</head>
<body>
    <h2>Quên mật khẩu</h2>

    @if (session('status'))
        <div style="color: green;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            <div>
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}"  autofocus><br><br>
        <button type="submit">Gửi link đặt lại mật khẩu</button>
    </form>

    <br>
    <a href="{{ route('login') }}">Quay lại đăng nhập</a>
</body>
</html>
