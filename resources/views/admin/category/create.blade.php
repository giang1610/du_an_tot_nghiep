@extends('admin.layouts.app')

@section('title', 'Thêm danh mục mới')
@section('page-title', 'Thêm danh mục mới')

@section('content')

<style>
    .card-container {
        max-width: 800px;
        margin: 2rem auto;
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: none;
    }
    .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-title {
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        font-size: 1.25rem;
    }
    .card-body {
        padding: 1.5rem;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        color: #64748b;
        margin-bottom: 1.5rem;
        text-decoration: none;
        transition: color 0.2s;
    }
    .back-link:hover {
        color: #334155;
    }
    .back-link i {
        margin-right: 0.5rem;
        font-size: 1.25rem;
    }
    .form-label {
        font-weight: 500;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.925rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.625rem 1rem;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4d4dff;
        box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.1);
        background-color: #fff;
    }
    .text-danger {
        font-size: 0.825rem;
        margin-top: 0.25rem;
        color: #dc2626;
    }
    .alert {
        border-radius: 8px;
        padding: 0.875rem 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.925rem;
    }
    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    .btn-submit {
        background-color: #84cc16;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        color: #fff;
        transition: all 0.2s;
        width: 100%;
        margin-top: 0.5rem;
    }
    .btn-submit:hover {
        background-color: #65a30d;
        transform: translateY(-1px);
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .slug-prefix {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-right: none;
        border-radius: 8px 0 0 8px;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
    .slug-input {
        border-radius: 0 8px 8px 0 !important;
    }
</style>

<div class="card-container">
    <a href="/admin/categories" class="back-link">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách danh mục
    </a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Thêm danh mục mới</h2>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('categories.store') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Tên danh mục *</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="Ví dụ: Điện thoại di động" value="{{ old('name') }}">
                    @error('name')
                        <div class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">Đường dẫn *</label>
                    <div class="d-flex">
                        <span class="slug-prefix">/category/</span>
                        <input type="text" name="slug" id="slug" class="form-control slug-input" 
                               placeholder="Tự động tạo từ tên danh mục" value="{{ old('slug') }}">
                    </div>
                    @error('slug')
                        <div class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái *</label>
                    <select name="status" class="form-select">
                        <option value="1" selected>Hoạt động</option>
                        <option value="0">Tạm dừng</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle-fill me-2"></i> Thêm danh mục
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function slugify(text) {
        return text.toString().normalize('NFD')
            .replace(/đ/g, 'd').replace(/Đ/g, 'D')
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