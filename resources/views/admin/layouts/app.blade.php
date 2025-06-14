{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        .content-wrapper {
            margin-left: 250px;
        }
        @media (max-width: 767.98px) {
            .content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Hiển thị tên người dùng nếu đã đăng nhập -->
@auth
    <div>{{ Auth::user()->name }}</div>
@endauth

<!-- Hiển thị link đăng nhập nếu chưa đăng nhập -->
@guest
<a href="{{ route('login') }}">Đăng nhập</a>
@endguest

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar bg-dark text-white w-250 p-5" id="sidebar">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="h4 mb-0">Admin Panel</h2>
            <button class="btn btn-dark d-md-none" id="toggleSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('categories.index') }}">
                        <i class="fas fa-home me-2"></i> Danh mục
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('products.index') }}">
                        <i class="fas fa-users me-2"></i> Sản phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="">
                        <i class="fas fa-cog me-2"></i> Thuộc tính
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="content-wrapper flex-grow-1">
        <!-- Header -->
        <header class="bg-white shadow-sm p-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-3" id="toggleSidebarBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h5 mb-0">@yield('page-title')</h1>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, {{ Auth::user()->name ?? 'Admin' }}</span>
                <a href="/logout" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <!-- Content -->
        <main class="p-4">
            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<!-- Sidebar Toggle Script -->
<script>
    const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = document.getElementById('toggleSidebar');

    toggleSidebarBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-hidden');
    });

    toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-hidden');
    });
</script>
</body>

</html> --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <!-- Bootstrap CSS -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> --}}
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
            /* Darker gradient */
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .sidebar .nav-link {
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 1.05rem;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .content-wrapper {
            margin-left: 260px;
            transition: margin-left 0.3s ease-in-out;
        }

        header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
        }

        header .btn-outline-danger {
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        header .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        main {
            padding: 30px;
        }

        .btn-light,
        .btn-dark {
            border-radius: 50%;
            padding: 8px;
            line-height: 1;
        }

        @media (max-width: 767.98px) {
            .content-wrapper {
                margin-left: 0;
            }

            .sidebar {
                width: 100%;
                max-width: 280px;
            }

            header .h5 {
                font-size: 1.1rem;
            }
        }
    </style>
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #4b5563;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #1f2937;
            transform: translateX(-3px);
        }

        .back-link i {
            font-size: 1.3rem;
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="sidebar p-4" id="sidebar">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <h2 class="h4 mb-0 fw-bold">Admin Panel</h2>
                <button class="btn btn-dark d-md-none" id="toggleSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('categories.index') }}">
                            <i class="fas fa-home"></i> Danh mục
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('products.index') }}">
                            <i class="fas fa-box"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('orders.index') }}" class="nav-link text-white">
                            <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="">
                            <i class="fas fa-cog"></i> Thuộc tính
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="content-wrapper flex-grow-1">
            <!-- Header -->
            <header class="bg-white shadow-sm d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-md-none me-3" id="toggleSidebarBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="h5 mb-0">@yield('page-title')</h1>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">Welcome, {{ Auth::user()->name ?? 'Admin' }}</span>
                    <!-- <a href="/logout" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a> -->
                    <form action="{{ route('logout') }}" class="btn btn-outline-danger btn-sm" method="POST">
                        @csrf
                        <button type="submit">Logout</button>
                         <!-- <i class="fas fa-sign-out-alt"></i> Logout -->
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script> --}}
    <!-- Sidebar Toggle Script -->
    <script>
        const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');

        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-hidden');
        });

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-hidden');
        });
    </script>
</body>

</html>