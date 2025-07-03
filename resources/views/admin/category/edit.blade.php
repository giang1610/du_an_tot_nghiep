@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Chỉnh sửa danh mục</h1>
        <a href="/admin/categories" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('categories.update', $category->id) }}" method="post">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold">Tên danh mục</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg" 
                           placeholder="Nhập tên danh mục" value="{{ $category->name }}">
                    @error('name')
                        <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i> {{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="slug" class="form-label fw-bold">Đường dẫn</label>
                    <div class="input-group">
                        <span class="input-group-text">/category/</span>
                        <input type="text" name="slug" id="slug" class="form-control" 
                               placeholder="Đường dẫn tự động" value="{{ $category->slug }}">
                    </div>
                    @error('slug')
                        <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i> {{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ old('status', $category->status) == '1' ? 'selected' : '' }}>
                            <i class="bi bi-check-circle me-1"></i> Hoạt động
                        </option>
                        <option value="0" {{ old('status', $category->status) == '0' ? 'selected' : '' }}>
                            <i class="bi bi-pause-circle me-1"></i> Tạm dừng
                        </option>
                    </select>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary me-md-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Lưu thay đổi
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
