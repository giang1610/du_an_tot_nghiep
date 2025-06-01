@extends('admin.layouts.app')

@section('content')
<a href="/products">
    <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i> Quay lại danh sách sản phẩm
</a>

<div class="container">
    <h1 class="h4 mb-4">Chỉnh sửa sản phẩm</h1>

    @if (session('error'))
    <div class="mb-0 alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('products.update', $product->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label h4">Tên sản phẩm</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}">
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label h4">Đường dẫn</label>
            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
            @error('slug') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label h4">Mô tả sản phẩm</label>
            <input type="text" name="description" class="form-control" value="{{ old('description', $product->description) }}">
            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label h4">Mô tả ngắn</label>
            <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}">
            @error('short_description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label><br>
            <img id="img" src="{{ asset('storage/'.$product->thumbnail) }}" style="max-width: 100px;"><br>
            <input type="file" name="thumbnail" class="form-control" onchange="img.src = window.URL.createObjectURL(this.files[0])">
            @error('thumbnail') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Màu sắc</label>
            <select name="color_id[]" class="form-select select2-color" multiple>
                @foreach($colors as $color)
                    <option value="{{ $color->id }}"
                        {{ in_array($color->id, old('color_id', $product->colors->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $color->name }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-color">Chọn tất cả màu</button>
        </div>

        <div class="mb-3">
            <label class="form-label">Kích cỡ</label>
            <select name="size_id[]" class="form-select select2-size" multiple>
                @foreach($sizes as $size)
                    <option value="{{ $size->id }}"
                        {{ in_array($size->id, old('size_id', $product->sizes->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $size->name }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-size">Chọn tất cả size</button>
        </div>

        <input type="hidden" name="has_variants" value="1">
        <div id="variantContainer">
            @foreach($product->variants as $index => $variant)
                <div class="card p-3 mb-3 border" id="variant-{{ $index }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ $variant->color->name ?? '' }} - {{ $variant->size->name ?? '' }}</strong>
                        <div>
                            <button type="button" class="btn btn-sm btn-warning btn-edit" data-target="#details-variant-{{ $index }}">Sửa</button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-target="#variant-{{ $index }}">Xóa</button>
                        </div>
                    </div>

                    <input type="hidden" name="variants[{{ $index }}][color_id]" value="{{ $variant->color_id }}">
                    <input type="hidden" name="variants[{{ $index }}][size_id]" value="{{ $variant->size_id }}">

                    <div class="variant-details mt-3" id="details-variant-{{ $index }}" style="display: none;">
                        <div class="mb-2">
                            <label>Mã SKU</label>
                            <input type="text" name="variants[{{ $index }}][sku]" class="form-control" value="{{ $variant->sku }}">
                        </div>

                        <div class="mb-2">
                            <label>Giá</label>
                            <input type="number" name="variants[{{ $index }}][price]" class="form-control" value="{{ $variant->price }}">
                        </div>

                        <div class="mb-2">
                            <label>Giá khuyến mãi</label>
                            <input type="number" name="variants[{{ $index }}][sale_price]" class="form-control" value="{{ $variant->sale_price }}">
                        </div>

                        <div class="mb-2">
                            <label>Ngày bắt đầu khuyến mãi</label>
                            <input type="datetime-local" name="variants[{{ $index }}][sale_start_date]" class="form-control" value="{{ $variant->sale_start_date ? \Carbon\Carbon::parse($variant->sale_start_date)->format('Y-m-d\TH:i') : '' }}">
                        </div>

                        <div class="mb-2">
                            <label>Ngày kết thúc khuyến mãi</label>
                            <input type="datetime-local" name="variants[{{ $index }}][sale_end_date]" class="form-control" value="{{ $variant->sale_end_date ? \Carbon\Carbon::parse($variant->sale_end_date)->format('Y-m-d\TH:i') : '' }}">
                        </div>

                        <div class="mb-2">
                            <label>Kho</label>
                            <select name="variants[{{ $index }}][stock]" class="form-select">
                                <option value="0" {{ $variant->stock == 0 ? 'selected' : '' }}>Còn hàng</option>
                                <option value="1" {{ $variant->stock == 1 ? 'selected' : '' }}>Hết hàng</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Ảnh</label><br>
                            <img src="{{ $variant->image ? asset('storage/'.$variant->image) : '' }}" class="preview-image mb-2" style="max-width: 100px;"><br>
                            <input type="file" name="variants[{{ $index }}][image]" class="form-control" onchange="previewImage(this)">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select" required>
                <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Chưa xuất bản</option>
                <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Hoạt động</option>
                <option value="2" {{ $product->status == 2 ? 'selected' : '' }}>Tạm dừng</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </div>
    </form>
</div>

{{-- Script giữ nguyên --}}
<script>
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.dataset.target);
            if (target.style.display === 'none') {
                target.style.display = 'block';
                this.textContent = 'Ẩn';
            } else {
                target.style.display = 'none';
                this.textContent = 'Sửa';
            }
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const variantCard = document.querySelector(this.dataset.target);
            variantCard.remove();
        });
    });

    function previewImage(input) {
        const img = input.closest('.mb-2').querySelector('.preview-image');
        if (input.files && input.files[0]) {
            img.src = URL.createObjectURL(input.files[0]);
        }
    }

    $(document).ready(function () {
        $('.select2-color').select2({ placeholder: "Chọn màu sắc", allowClear: true });
        $('.select2-size').select2({ placeholder: "Chọn kích cỡ", allowClear: true });

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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
