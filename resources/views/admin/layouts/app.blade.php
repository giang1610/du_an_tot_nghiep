<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Admin Dashboard')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Poppins + Bootstrap + Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    :root{--full:260px;--mini:72px;--radius:10px;--primary:#2563eb;--active-dark:#1e2a48}

    body{margin:0;font-family:'Poppins',sans-serif;background:#f4f7fe;color:#1f2937}

    /* -------- SIDEBAR -------- */
    .sidebar{width:var(--full);min-height:100vh;background:#fff;border-right:1px solid #e5e7eb;
             position:fixed;top:0;left:0;transition:width .25s;overflow-x:hidden}
    .sidebar.collapsed{width:var(--mini)}
    .sidebar.expanded-hover{width:var(--full)!important}
    .sidebar.expanded-hover~.content-wrapper{margin-left:var(--full)!important}

    .brand{padding:20px;display:flex;align-items:center;gap:10px}
    .brand i{font-size:1.6rem;color:var(--primary)}
    .brand-text{font-size:1.25rem;font-weight:600}
    .brand-mini{display:none;font-size:1.6rem}
    .sidebar.collapsed:not(.expanded-hover) .brand{flex-direction:column}
    .sidebar.collapsed:not(.expanded-hover) .brand-text{display:none}
    .sidebar.collapsed:not(.expanded-hover) .brand-mini{display:block;margin-top:4px}

    .menu-label{font-size:.7rem;font-weight:600;letter-spacing:.05em;padding-left:1.5rem;margin-bottom:.4rem;color:#6b7280}
    .sidebar.collapsed:not(.expanded-hover) .menu-label{display:none}

    .nav-link{padding:12px 18px;border-radius:var(--radius);display:flex;align-items:center;gap:12px;font-weight:500;color:#1f2937}
    .nav-link i{width:20px;text-align:center;color:#6b7280}
    .nav-link:hover{background:#e5e7eb;color:var(--primary)}
    .nav-link:hover i{color:var(--primary)}
    .nav-link.active{background:#e5edff;color:#1d4ed8;font-weight:600}
    .nav-link.active i{color:#1d4ed8}

    .sidebar.collapsed:not(.expanded-hover) .menu-text,
    .sidebar.collapsed:not(.expanded-hover) .bi-chevron-down{display:none}
    .sidebar.collapsed:not(.expanded-hover) .nav-link{justify-content:center;padding:12px 0}

    /* -------- CONTENT WRAPPER -------- */
    .content-wrapper{margin-left:var(--full);transition:margin-left .25s}
    .sidebar.collapsed~.content-wrapper{margin-left:var(--mini)}
    @media(max-width:767.98px){.content-wrapper{margin-left:0}}

    /* -------- TOPBAR -------- */
    .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;
            display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
    .btn-icon{width:44px;height:44px;border:1px solid #d1d5db;border-radius:var(--radius);
              display:flex;align-items:center;justify-content:center;background:#fff;color:#6b7280}
    .btn-icon:hover{background:#f1f5f9}


    .search-box{flex-grow:1;min-width:250px;max-width:100%;position:relative}
    .search-box .form-control{width:100%;height:44px;border:1px solid #d1d5db;border-radius:var(--radius);
                              padding-left:44px;padding-right:60px;font-size:.95rem}
    .bi-search{position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:1.2rem;color:#6b7280}
    .kbd-hint{position:absolute;right:14px;top:50%;transform:translateY(-50%);
              font-size:.75rem;color:#6b7280;border:1px solid #d1d5db;border-radius:6px;padding:2px 6px}

    .avatar{width:38px;height:38px;border-radius:50%;
            background:#cbd5e1 url('https://abs.twimg.com/sticky/default_profile_images/default_profile_400x400.png') center/cover no-repeat}

    .dropdown-menu{border-radius:var(--radius);padding:.75rem;min-width:230px;font-size:.9rem}
    .dropdown-item i{width:20px;text-align:center;margin-right:6px}

    /* -------- DARK MODE -------- */
    body:not(.dark-mode) .admin-name{color:#000}
    .dark-mode{background:#1e1e2f!important;color:#f8fafc}
    .dark-mode .sidebar{background:#2c2f48;border-color:#3b3f63}
    .dark-mode .topbar{background:#2f3148;border-bottom-color:#3b3f63}
    .dark-mode .nav-link{color:#cbd5e1}
    .dark-mode .nav-link i{color:#9ca3af}
    .dark-mode .nav-link:hover{background:rgba(255,255,255,.05);color:#60a5fa}
    .dark-mode .nav-link:hover i{color:#60a5fa}
    .dark-mode .nav-link.active{background:var(--active-dark);color:#fff}
    .dark-mode .nav-link.active i{color:#fff}
    .dark-mode .menu-label,.dark-mode .bi-search,.dark-mode .kbd-hint{color:#9ca3af}
    .dark-mode .search-box .form-control{background:#1f2937;border-color:#3b3f63;color:#f8fafc}
    .dark-mode .search-box .form-control::placeholder{color:#9ca3af}
    .dark-mode .btn-icon{background:#1f2937;border-color:#3b3f63;color:#cbd5e1}
    .dark-mode .admin-name{color:#fff}
  </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside id="sidebar" class="sidebar">
  <div class="brand">
    <i class="bi bi-bar-chart-fill"></i>
    <span class="brand-text">Admin Panel</span>
    <span class="brand-mini">…</span>
  </div>

  <div class="menu-label">MENU</div>
  <ul class="nav flex-column px-2" id="menuList">
    <!-- Dashboard -->
    <li class="nav-item menu-item" data-title="dashboard">
      <a href="{{ route('admin') }}" class="nav-link active">
        <i class="bi bi-grid"></i><span class="menu-text">Dashboard</span>
      </a>
    </li>

    <!-- Danh mục -->
    <li class="nav-item menu-parent" data-title="categories">
      <a class="nav-link" data-bs-toggle="collapse" href="#catMenu">
        <i class="bi bi-folder"></i><span class="menu-text">Danh mục</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <div id="catMenu" class="collapse ps-3">
        <a href="{{ route('categories.index') }}" class="nav-link py-2 menu-item" data-title="danh sách danh mục">Danh sách</a>
        <a href="{{ route('categories.create') }}" class="nav-link py-2 menu-item" data-title="thêm danh mục">Thêm mới</a>
      </div>
    </li>

    <!-- Sản phẩm -->
    <li class="nav-item menu-parent" data-title="products">
      <a class="nav-link" data-bs-toggle="collapse" href="#prodMenu">
        <i class="bi bi-box-seam"></i><span class="menu-text">Sản phẩm</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <div id="prodMenu" class="collapse ps-3">
        <a href="{{ route('products.index') }}" class="nav-link py-2 menu-item" data-title="danh sách sản phẩm">Danh sách</a>
        <a href="{{ route('products.create') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Thêm mới</a>
      </div>
    </li>

    <!-- Đơn hàng -->
     <li class="nav-item menu-parent" data-title="orders">
      <a class="nav-link" data-bs-toggle="collapse" href="#ordersMenu">
        <i class="bi bi-receipt"></i><span class="menu-text">Đơn hàng</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <div id="ordersMenu" class="collapse ps-3">
        <a href="{{ route('orders.index') }}" class="nav-link py-2 menu-item" data-title="danh sách sản phẩm">Danh sách</a>
        <a href="{{ route('orders.cancelled') }}" class="nav-link py-2 menu-item" data-title="danh sách sản phẩm">Đơn đã huỷ</a>
        <a href="{{ route('orders.pending') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Đơn chờ xử lí</a>
        <a href="{{ route('orders.processing') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Đơn đang xử lý</a>
        <a href="{{ route('orders.picking') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Đang lấy hàng</a>
        <a href="{{ route('orders.shipping') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Đang giao hàng</a>
        <a href="{{ route('orders.shipped') }}" class="nav-link py-2 menu-item" data-title="thêm sản phẩm">Đã giao hàng</a>
      
    </li>

    <!-- Đơn hàng
    <li class="nav-item menu-item" data-title="orders">
      <a href="{{ route('orders.index') }}" class="nav-link">
        <i class="bi bi-receipt"></i><span class="menu-text">Đơn hàng</span>
      </a>
    </li> -->

    <!-- 👉 Khách hàng (mới, chưa có route) -->
    <li class="nav-item menu-item" data-title="khách hàng customers">
      <a href="{{ route('users.index') }}" class="nav-link">
        <i class="bi bi-people"></i><span class="menu-text">Khách hàng</span>
      </a>
    </li>

    <!-- 👉 Doanh thu (mới, chưa có route) -->
    <li class="nav-item menu-item" data-title="doanh thu revenue">
      <a href="#" class="nav-link">
        <i class="bi bi-cash-stack"></i><span class="menu-text">Doanh thu</span>
      </a>
    </li>
  </ul>
</aside>

<!-- ===== MAIN WRAPPER ===== -->
<div class="content-wrapper">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button id="toggleBtn" class="btn btn-icon"><i class="bi bi-list"></i></button>

        <!-- <div class="search-box">
            <i class="bi bi-search"></i>
            <input id="globalSearch" class="form-control" placeholder="Tìm kiếm">
            <span class="kbd-hint" id="searchTrigger">⌘ K</span>
        </div> -->
    </div>

    <div class="d-flex align-items-center gap-3">
      <button id="darkModeToggle" class="btn btn-icon"><i class="bi bi-moon"></i></button>
      <button class="btn-icon position-relative"><i class="bi bi-bell"></i></button>

      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
          <div class="avatar"></div>
          <span class="admin-name fw-medium d-none d-md-inline">{{ Auth::user()->name ?? 'Admin' }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header">{{ Auth::user()->name ?? 'Admin' }}<br><small class="text-muted">{{ Auth::user()->email ?? 'admin@example.com' }}</small></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Hồ sơ</a></li>
          <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Cài đặt</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form action="{{ route('logout') }}" method="POST">@csrf
              <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="pt-1" id="mainContent">
    @yield('content')
  </main>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar'),
        toggleBtn = document.getElementById('toggleBtn');
  let collapsed = false;
  toggleBtn.onclick = () => {
    sidebar.classList.toggle('collapsed');
    collapsed = sidebar.classList.contains('collapsed');
  };
  sidebar.onmouseenter = () => { if(collapsed) sidebar.classList.add('expanded-hover'); };
  sidebar.onmouseleave = () => { sidebar.classList.remove('expanded-hover'); };

  const body = document.body,
        darkBtn = document.getElementById('darkModeToggle');
  darkBtn.onclick = () => {
    body.classList.toggle('dark-mode');
    darkBtn.firstElementChild.classList.toggle('bi-sun');
    darkBtn.firstElementChild.classList.toggle('bi-moon');
  };

  /* Search */
  const menuItems = document.querySelectorAll('#menuList .menu-item,#menuList .menu-parent'),
        globalSearch = document.getElementById('globalSearch'),
        searchTrigger = document.getElementById('searchTrigger'),
        content = document.getElementById('mainContent');

  function highlight(container, kw) {
    container.querySelectorAll('mark').forEach(m => m.outerHTML = m.innerText);
    if(!kw) return;
    [...container.querySelectorAll('*')]
      .filter(n => n.childNodes.length===1 && n.childNodes[0].nodeType===3)
      .forEach(n => n.innerHTML = n.textContent.replace(new RegExp(`(${kw})`, 'gi'), '<mark>$1</mark>'));
  }

  function doSearch() {
    const kw = globalSearch.value.toLowerCase().trim();
    menuItems.forEach(el => {
      const t = el.dataset.title || '';
      el.style.display = kw && !t.includes(kw) ? 'none' : '';
    });
    highlight(content, kw);
  }

  searchTrigger.onclick = doSearch;
  globalSearch.onkeydown = e => { if(e.key === 'Enter') doSearch(); };
</script>
</body>
</html>