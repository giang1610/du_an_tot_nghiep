@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa danh mục')
@section('page-title', 'Chỉnh sửa danh mục')

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
    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }
    .btn-submit {
        background-color: #3716cc;
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
        background-color: #081b86;
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
    .btn-reset {
        background-color: #f1f5f9;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        color: #64748b;
        transition: all 0.2s;
        width: 100%;
        margin-top: 0.5rem;
    }
    .btn-reset:hover {
        background-color: #e2e8f0;
        color: #334155;
    }
    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
</style>

<div class="card-container">
    <a href="/admin/categories" class="back-link">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách danh mục
    </a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Chỉnh sửa danh mục</h2>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('categories.update', $category->id) }}" method="post">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name" class="form-label">Tên danh mục *</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="Ví dụ: Điện thoại di động" value="{{ old('name', $category->name) }}">
                    @error('name')
                        <div class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">Đường dẫn *</label>
                    <div class="d-flex">
                        <span class="slug-prefix">/category/</span>
                        <input type="text" name="slug" id="slug" class="form-control slug-input" 
                               placeholder="Tự động tạo từ tên danh mục" value="{{ old('slug', $category->slug) }}">
                    </div>
                    @error('slug')
                        <div class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái *</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>Tạm dừng</option>
                    </select>
                </div>

                <div class="button-group">
                    <button type="reset" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Đặt lại
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle-fill me-2"></i> Cập nhật
                    </button>
                </div>
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
