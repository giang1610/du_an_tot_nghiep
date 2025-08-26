<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng nhập</h2>

        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <div class="list-disc text-center">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" 
                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Mật khẩu + toggle --}}
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

            {{-- Ghi nhớ --}}
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition duration-200">
                Đăng nhập
            </button>
        </form>

        {{-- Liên kết dưới --}}
        <div class="mt-4 text-center text-sm text-gray-600">
            {{-- <p><a href="{{ route('register') }}" class="text-blue-600 hover:underline">Chưa có tài khoản? Đăng ký</a></p> --}}
            <p><a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Quên mật khẩu?</a></p>
        </div>
    </div>

    {{-- Script toggle hiển thị mật khẩu --}}
    <script>
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');

        toggleBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleBtn.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>
