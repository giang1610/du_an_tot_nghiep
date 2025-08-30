<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng ký</h2>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <div class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            {{-- Tên --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Tên:</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"  autofocus
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Mật khẩu --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu:</label>
                <div class="relative">
                    <input type="password" name="password" id="password" 
                           class="mt-1 w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-800 focus:outline-none"
                            tabindex="-1">
                        👁️
                    </button>
                </div>
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu:</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="mt-1 w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="togglePasswordConfirm"
                            class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-800 focus:outline-none"
                            tabindex="-1">
                        👁️
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition duration-200">
                Đăng ký
            </button>
        </form>

        {{-- Liên kết chuyển trang --}}
        <div class="mt-4 text-center text-sm text-gray-600">
            <p><a href="{{ route('login') }}" class="text-blue-600 hover:underline">Đã có tài khoản? Đăng nhập</a></p>
        </div>
    </div>

    {{-- Script toggle mật khẩu --}}
    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');

        togglePasswordBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePasswordBtn.textContent = type === 'password' ? '👁️' : '🙈';
        });

        const passwordConfirmInput = document.getElementById('password_confirmation');
        const toggleConfirmBtn = document.getElementById('togglePasswordConfirm');

        toggleConfirmBtn.addEventListener('click', () => {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
            toggleConfirmBtn.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>
