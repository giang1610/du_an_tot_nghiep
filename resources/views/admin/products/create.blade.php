@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/products" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <h1 class="h4 mb-4">Thêm sản phẩm</h1>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-medium">Tên sản phẩm</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên sản phẩm" value="{{ old('name') }}">
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-medium">Đường dẫn danh mục</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn tự động tạo" value="{{ old('slug') }}">
                        @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-medium">Mô tả sản phẩm</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả sản phẩm" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="short_description" class="form-label fw-medium">Mô tả ngắn</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description') }}">
                        @error('short_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="thumbnail" class="form-label fw-medium">Ảnh sản phẩm</label>
                        <div class="mb-2">
                            <img id="img" class="rounded" style="max-width: 100px;" src="" alt="Preview">
                        </div>
                        <input type="file" name="thumbnail" class="form-control" onchange="img.src = window.URL.createObjectURL(this.files[0])">
                        @error('thumbnail')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-medium">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="colorSelect" class="form-label fw-medium">Màu sắc</label>
                        <select name="color_id[]" class="form-select select2-color" multiple id="colorSelect">
                            @foreach($colors as $color)
                            <option value="{{ $color->id }}" data-name="{{ $color->name }}" {{ in_array($color->id, old('color_id', [])) ? 'selected' : '' }}>{{ $color->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-color">Chọn tất cả màu</button>
                        @error('color_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="sizeSelect" class="form-label fw-medium">Kích cỡ</label>
                        <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
                            @foreach($sizes as $size)
                            <option value="{{ $size->id }}" data-name="{{ $size->name }}" {{ in_array($size->id, old('size_id', [])) ? 'selected' : '' }}>{{ $size->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-size">Chọn tất cả size</button>
                        @error('size_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="status" class="form-label fw-medium">Trạng thái</label>
                        <select name="status" class="form-select" required>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                        @error('status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-primary" id="generateVariants">Tạo các biến thể</button>
            <input type="hidden" name="has_variants" value="1">
        </div>

        <div id="variantContainer"></div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
        </div>
    </form>
</div>

<script>
document.getElementById('generateVariants').addEventListener('click', function () {
    const colors = Array.from(document.querySelectorAll('#colorSelect option:checked'));
    const sizes = Array.from(document.querySelectorAll('#sizeSelect option:checked'));
    const container = document.getElementById('variantContainer');
    container.innerHTML = ''; // Clear previous variants

    let index = 0;
    colors.forEach(color => {
        sizes.forEach(size => {
            const variantId = `variant-${index}`;
            const html = `
                <div class="card p-3 mb-3 border shadow-sm" id="${variantId}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">${color.dataset.name} - ${size.dataset.name}</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-bs-target="#details-${variantId}">Sửa</button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-target="#${variantId}">Xóa</button>
                        </div>
                    </div>
                    <input type="hidden" name="variants[${index}][color_id]" value="${color.value}">
                    <input type="hidden" name="variants[${index}][size_id]" value="${size.value}">
                    <div class="variant-details mt-2" id="details-${variantId}" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Mã SKU</label>
                                <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Kho</label>
                                <select name="variants[${index}][stock]" class="form-select form-select-sm">
                                    <option value="">-- Chọn kho --</option>
                                    <option value="0">Còn hàng</option>
                                    <option value="1">Hết hàng</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Giá</label>
                                <input type="number" name="variants[${index}][price]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Giá khuyến mãi</label>
                                <input type="number" name="variants[${index}][sale_price]" class="form-control form-control-sm" id="sale_price_${index}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                <input type="datetime-local" name="variants[${index}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_${index}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                <input type="datetime-local" name="variants[${index}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_${index}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Ảnh</label>
                                <div class="mb-2">
                                    <img class="preview-image mb-2 rounded" style="max-width: 100px;" src="" alt="Preview">
                                </div>
                                <input type="file" name="variants[${index}][image]" class="form-control form-control-sm" onchange="previewImage(this)">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            index++;
        });
    });

    // Event for "Sửa" button to toggle details
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const detailSection = document.querySelector(this.dataset.bsTarget);
            if (detailSection.style.display === 'none') {
                detailSection.style.display = 'block';
                this.textContent = 'Ẩn';
            } else {
                detailSection.style.display = 'none';
                this.textContent = 'Sửa';
            }
        });
    });

    // Event for "Xóa" button to remove variant
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            const variantCard = document.querySelector(this.dataset.bsTarget);
            variantCard.remove();
        });
    });
});

function previewImage(input) {
    const img = input.closest('.col-12').querySelector('.preview-image');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
    }
}

// Slug generation
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

// Enable/disable sale date inputs based on sale price
document.addEventListener('input', function(e) {
    if (e.target.name && e.target.name.includes('[sale_price]')) {
        const index = e.target.name.match(/\[(\d+)\]/)[1];
        const startDateInput = document.getElementById(`sale_start_date_${index}`);
        const endDateInput = document.getElementById(`sale_end_date_${index}`);
        if (e.target.value.trim() !== '') {
            startDateInput.disabled = false;
            endDateInput.disabled = false;
        } else {
            startDateInput.disabled = true;
            endDateInput.disabled = true;
            startDateInput.value = '';
            endDateInput.value = '';
        }
    }
});
</script>

<!-- Bootstrap Icons and Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<!-- jQuery and Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    $('.select2-color').select2({
        placeholder: "Chọn màu sắc",
        allowClear: true
    });

    $('.select2-size').select2({
        placeholder: "Chọn kích cỡ",
        allowClear: true
    });

    $('.select-all').on('click', function () {
        const target = $(this).data('target');
        const select = $(target);
        const allValues = [];
        select.find('option').each(function () {
            allValues.push($(this).val());
        });
        select.val(allValues).trigger('change');
    });
});
</script>
@endsection