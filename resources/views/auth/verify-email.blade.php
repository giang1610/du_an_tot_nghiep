<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh Email của bạn</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2; /* Xanh dương */
            --primary-hover: #357ABD;
            --success-color: #28a745; /* Xanh lá */
            --success-bg: #d4edda;
            --success-border: #c3e6cb;
            --danger-color: #dc3545; /* Đỏ */
            --danger-hover: #c82333;
            --text-dark: #333333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --border-light: #e9ecef;
            --shadow-light: rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .card {
            background-color: var(--bg-white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px var(--shadow-light);
            max-width: 480px;
            width: 100%;
            text-align: center;
            border: 1px solid var(--border-light);
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 2em;
            font-weight: 700;
        }

        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-color);
            border: 1px solid var(--success-border);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: left;
        }

        p {
            color: var(--text-light);
            margin-bottom: 18px;
        }

        .button {
            display: block;
            width: 100%;
            padding: 14px 25px;
            margin-top: 15px; /* Khoảng cách giữa các nút */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none; /* Đảm bảo không có gạch chân nếu dùng <a> */
            color: white; /* Màu chữ trắng */
        }

        .button-primary {
            background-color: var(--primary-color);
        }

        .button-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .button-logout {
            background-color: var(--danger-color);
            margin-top: 10px; /* Khoảng cách nhỏ hơn với nút trên */
        }

        .button-logout:hover {
            background-color: var(--danger-hover);
            transform: translateY(-2px);
        }

        form {
            margin-bottom: 0; /* Loại bỏ margin mặc định của form */
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Xác minh Email của bạn</h2>

        @if (session('status') == 'verification-link-sent')
            <div class="alert-success">
                ✅ Một link xác minh mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến (và thư mục spam).
            </div>
        @endif

        <p>
            Trước khi truy cập, vui lòng kiểm tra hộp thư email của bạn để tìm link xác minh.
            Nếu bạn không nhận được email, đừng lo lắng!
        </p>
        <p>
            Bạn có thể nhấn nút dưới đây để chúng tôi gửi lại một email khác.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="button button-primary">Gửi lại link xác minh</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="button button-logout">Đăng xuất</button>
        </form>
    </div>
</body>
</html>