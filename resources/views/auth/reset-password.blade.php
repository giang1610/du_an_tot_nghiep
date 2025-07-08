<!-- resources/views/auth/reset-password.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Đặt lại mật khẩu</title>
</head>
<body>
    <h2>Đặt lại mật khẩu</h2>

    @if ($errors->any())
        <div style="color: red;">
            <div>
                @foreach ($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}"  autofocus><br><br>

        <label>Mật khẩu mới:</label><br>
        <input type="password" name="password" ><br><br>

        <label>Xác nhận mật khẩu:</label><br>
        <input type="password" name="password_confirmation" ><br><br>

        <button type="submit">Đặt lại mật khẩu</button>
    </form>
</body>
</html>
