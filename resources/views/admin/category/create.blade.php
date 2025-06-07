{{-- @extends('admin.layouts.app')

@section('content')
<a href="/categories">
    <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i> Quay lại danh sách danh mục
</a>

<div class="container">
    <h1 class="h4 mb-4">Thêm danh mục mới</h1>

    @if (session('error'))
    <div class="mb-0 alert alert-success">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('categories.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label h4">Tên danh mục</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên danh mục" value="{{ old('name') }}">
            @error('name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label h4">Đường dẫn danh mục</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn danh mục tự động tạo" value="{{ old('slug') }}">
            @error('slug')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select" required>
                <option value="1" selected>Hoạt động</option>
                <option value="0">Tạm dừng</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </div>
    </form>
</div>
<script>
    // Hàm chuyển tiếng Việt có dấu thành không dấu và chuyển khoảng trắng thành dấu gạch ngang
    function slugify(text) {
        return text.toString().normalize('NFD') // tách dấu
            .replace(/[\u0300-\u036f]/g, '') // loại bỏ dấu
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '') // bỏ ký tự đặc biệt
            .replace(/\s+/g, '-') // thay khoảng trắng thành dấu -
            .replace(/-+/g, '-'); // loại bỏ dấu - liên tiếp
    }

    document.getElementById('name').addEventListener('input', function() {
        const nameValue = this.value;
        const slugValue = slugify(nameValue);
        document.getElementById('slug').value = slugValue;
    });
</script>
@endsection

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

 --}}
@extends('admin.layouts.app')

@section('title', 'Thêm danh mục mới')

@section('page-title', 'Thêm danh mục mới')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .container {
        max-width: 900px; /* Giữ chiều rộng */
        background: #ffffff;
        padding: 25px;
        border-radius: 10px;
        margin-top: 20px;
        /* margin-left: 0; */
    }
    .form-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
    }
    .form-label {
        font-weight: 500;
        color: #374151;
        font-size: 0.95rem;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 6px;
        padding: 10px;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background: #ffffff;
        outline: none;
    }
    .text-danger {
        font-size: 0.85rem;
        margin-top: 4px;
        color: #dc2626;
    }
    .alert-success {
        background-color: #ecfdf5;
        color: #064e3b;
        border: 1px solid #6ee7b7;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    .btn-primary {
        background: #10b981;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        width: 100%;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #059669;
        transform: translateY(-1px);
    }
    .form-group {
        margin-bottom: 20px;
    }
</style>

<div class="container">
    <a href="/categories" class="back-link">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách danh mục
    </a>

    <h1 class="form-title">Thêm danh mục mới</h1>

    @if (session('error'))
    <div class="alert alert-success">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('categories.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">Tên danh mục</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên danh mục" value="{{ old('name') }}">
            @error('name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="slug" class="form-label">Đường dẫn danh mục</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn danh mục tự động tạo" value="{{ old('slug') }}">
            @error('slug')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="1" selected>Hoạt động</option>
                <option value="0">Tạm dừng</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </div>
    </form>
</div>

<script>
    function slugify(text) {
        return text.toString().normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    document.getElementById('name').addEventListener('input', function() {
        const nameValue = this.value;
        const slugValue = slugify(nameValue);
        document.getElementById('slug').value = slugValue;
    });
</script>
@endsection