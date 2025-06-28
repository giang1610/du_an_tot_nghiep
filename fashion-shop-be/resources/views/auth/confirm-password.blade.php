@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Xác nhận mật khẩu</h2>
    <p>Vui lòng xác nhận mật khẩu của bạn trước khi tiếp tục.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
            @error('password') <div>{{ $message }}</div> @enderror
        </div>

        <button type="submit">Xác nhận</button>
    </form>
</div>
@endsection
