@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        {{-- Nút quay lại danh sách sản phẩm --}}
        <a href="{{ route('products.index') }}" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    {{-- Tiêu đề trang chỉnh sửa sản phẩm --}}
    <h1 class="h4 mb-4">Chỉnh sửa sản phẩm: {{ $product->name }}</h1>

    {{-- Hiển thị thông báo lỗi (nếu có) --}}
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Form chỉnh sửa sản phẩm --}}
    <form action="{{ route('products.update', $product->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT') {{-- Sử dụng phương thức PUT cho cập nhật --}}

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Trường Tên sản phẩm --}}
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-medium">Tên sản phẩm</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên sản phẩm" value="{{ old('name', $product->name) }}">
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Đường dẫn danh mục (Slug) --}}
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-medium">Đường dẫn danh mục</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn tự động tạo" value="{{ old('slug', $product->slug) }}">
                        @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Mô tả sản phẩm --}}
                    <div class="col-12">
                        <label for="description" class="form-label fw-medium">Mô tả sản phẩm</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả sản phẩm" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Mô tả ngắn --}}
                    <div class="col-6">
                        <label for="short_description" class="form-label fw-medium">Mô tả ngắn</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description', $product->short_description) }}">
                        @error('short_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Giá sản phẩm --}}
                    <div class="col-6">
                        <label for="price_products" class="form-label fw-medium">Giá sản phẩm</label>
                        <input type="number" name="price_products" id="price_products" class="form-control" placeholder="Nhập giá sản phẩm" value="{{ old('price_products', $product->price_products) }}">
                        @error('price_products')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ảnh sản phẩm chính --}}
                    <div class="col-md-6">
                        <label for="thumbnail" class="form-label">Ảnh sản phẩm</label> <br>
                        <img id="img" src="{{ old('thumbnail_old', $product->thumbnail ? asset('storage/'.$product->thumbnail) : '') }}" style="max-width: 100px;"> <br>
                        <input type="file" name="thumbnail" class="form-control" onchange="img.src = window.URL.createObjectURL(this.files[0])">
                        <input type="hidden" name="thumbnail_old" value="{{ $product->thumbnail }}">
                        @error('thumbnail')
                        <div class="text-danger">{{$message}}</div>
                        @enderror
                    </div>

                    {{-- Trường Danh mục --}}
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-medium">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Trường Màu sắc (Select2) --}}
                    <div class="col-md-6">
                        <label for="colorSelect" class="form-label fw-medium">Màu sắc</label>
                        <select name="color_id[]" class="form-select select2-color" multiple id="colorSelect">
                            @foreach($colors as $color)
                                {{-- Kiểm tra nếu màu đã được chọn từ dữ liệu cũ (sau lỗi validate) hoặc từ sản phẩm hiện có --}}
                                <option value="{{ $color->id }}" data-name="{{ $color->name }}"
                                    {{ in_array($color->id, old('color_id', $product->colors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $color->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-color">Chọn tất cả màu</button>
                        @error('color_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Kích cỡ (Select2) --}}
                    <div class="col-md-6">
                        <label for="sizeSelect" class="form-label fw-medium">Kích cỡ</label>
                        <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
                            @foreach($sizes as $size)
                                {{-- Kiểm tra nếu kích cỡ đã được chọn từ dữ liệu cũ (sau lỗi validate) hoặc từ sản phẩm hiện có --}}
                                <option value="{{ $size->id }}" data-name="{{ $size->name }}"
                                    {{ in_array($size->id, old('size_id', $product->sizes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $size->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-size">Chọn tất cả size</button>
                        @error('size_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Trường Trạng thái --}}
                    <div class="col-12">
                        <label for="status" class="form-label fw-medium">Trạng thái</label>
                        <select name="status" class="form-select" required>
                            <option value="0" {{ old('status', $product->status) == '0' ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status', $product->status) == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ old('status', $product->status) == '2' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                        @error('status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            {{-- Nút "Tạo các biến thể" để tạo các thẻ biến thể mới --}}
            <button type="button" class="btn btn-primary" id="generateVariants">Tạo các biến thể mới</button>
            <input type="hidden" name="has_variants" value="1">
        </div>

        {{-- Container cho các biến thể hiện có và các biến thể mới được tạo --}}
        <div id="variantContainer">
            {{-- Kiểm tra nếu có dữ liệu biến thể cũ từ lỗi validate hoặc từ sản phẩm hiện có --}}
            @php
                $variantsToDisplay = empty(old('variants')) ? $product->variants->toArray() : old('variants');
            @endphp
            @foreach($variantsToDisplay as $i => $variant)
                <div class="card p-3 mb-3 border shadow-sm" id="variant-{{ $i }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        {{-- Hiển thị tên màu và kích cỡ của biến thể --}}
                        <h5 class="card-title mb-0">
                            {{ $colors->firstWhere('id', $variant['color_id'])?->name ?? 'Màu không xác định' }} -
                            {{ $sizes->firstWhere('id', $variant['size_id'])?->name ?? 'Kích cỡ không xác định' }}
                        </h5>
                        <div>
                            {{-- Nút để ẩn/hiện chi tiết biến thể --}}
                            <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-bs-target="#details-variant-{{ $i }}">Ẩn</button>
                            {{-- Nút để xóa biến thể (trước khi gửi form) --}}
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-target="#variant-{{ $i }}">Xóa</button>
                        </div>
                    </div>
                    {{-- Thêm input hidden cho id biến thể hiện có để phục vụ cập nhật --}}
                    @if (isset($variant['id']))
                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] }}">
                    @endif
                    <input type="hidden" name="variants[{{ $i }}][color_id]" value="{{ $variant['color_id'] }}">
                    <input type="hidden" name="variants[{{ $i }}][size_id]" value="{{ $variant['size_id'] }}">
                    {{-- Phần chi tiết biến thể có thể ẩn/hiện --}}
                    <div class="variant-details mt-2" id="details-variant-{{ $i }}" style="display: block;">
                        <div class="row g-3">
                            {{-- Trường Mã sản phẩm (SKU) --}}
                            <div class="col-md-6">
                                <label class="form-label small">Mã SP</label>
                                <input type="text" name="variants[{{ $i }}][sku]" class="form-control form-control-sm" value="{{ old('variants.'.$i.'.sku', $variant['sku'] ?? '') }}">
                                @error('variants.'.$i.'.sku')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Kho (Stock) --}}
                            <div class="col-md-6">
                                <label class="form-label small">Kho</label>
                                <select name="variants[{{ $i }}][stock]" class="form-select form-select-sm">
                                    <option value="0" {{ old('variants.'.$i.'.stock', $variant['stock'] ?? '') == '0' ? 'selected' : '' }}>Còn hàng</option>
                                    <option value="1" {{ old('variants.'.$i.'.stock', $variant['stock'] ?? '') == '1' ? 'selected' : '' }}>Hết hàng</option>
                                </select>
                                @error('variants.'.$i.'.stock')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Giá --}}
                            <div class="col-md-6">
                                <label class="form-label small">Giá</label>
                                <input type="number" name="variants[{{ $i }}][price]" class="form-control form-control-sm" value="{{ old('variants.'.$i.'.price', $variant['price'] ?? '') }}">
                                @error('variants.'.$i.'.price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Giá khuyến mãi --}}
                            <div class="col-md-6">
                                <label class="form-label small">Giá khuyến mãi</label>
                                <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control form-control-sm" id="sale_price_{{ $i }}" value="{{ old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '') }}">
                                @error('variants.'.$i.'.sale_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Ngày bắt đầu khuyến mãi --}}
                            <div class="col-md-6">
                                <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                <input type="datetime-local" name="variants[{{ $i }}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_{{ $i }}" value="{{ old('variants.'.$i.'.sale_start_date', (isset($variant['sale_start_date']) ? \Carbon\Carbon::parse($variant['sale_start_date'])->format('Y-m-d\TH:i') : '')) }}">
                                @error('variants.'.$i.'.sale_start_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Ngày kết thúc khuyến mãi --}}
                            <div class="col-md-6">
                                <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                <input type="datetime-local" name="variants[{{ $i }}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_{{ $i }}" value="{{ old('variants.'.$i.'.sale_end_date', (isset($variant['sale_end_date']) ? \Carbon\Carbon::parse($variant['sale_end_date'])->format('Y-m-d\TH:i') : '')) }}">
                                @error('variants.'.$i.'.sale_end_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Trường Ảnh biến thể --}}
                            <div class="col-12">
                                <label class="form-label small">Ảnh biến thể</label>
                                <div class="mb-2">
                                    {{-- Hiển thị ảnh biến thể hiện có hoặc ảnh đã chọn nếu có lỗi validate --}}
                                    <img class="preview-image mb-2 rounded" style="max-width: 100px;"
                                    src="{{ isset($variant['image']) && $variant['image'] ? asset('storage/'.$variant['image']) : '' }}"
                                    alt="Preview" id="img-preview-{{ $i }}">
                                </div>
                                </div>
                                <input type="file" name="variants[{{ $i }}][image]" class="form-control form-control-sm" onchange="document.getElementById('img-preview-{{ $i }}').src = window.URL.createObjectURL(this.files[0])">
                                <input type="file" name="variants[{{ $i }}][image_old]" value="{{ $variant['image'] ?? '' }}">
                                @error('variants.'.$i.'.image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div> <br> <br>

        <div class="mt-4 product-update-button-container"> {{-- Thêm class mới --}}
        <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
        </div>
    </form>
</div>

{{-- JavaScript cho chức năng biến thể, slug và Select2 --}}
<script>
    // Hàm slugify để tạo slug từ tên sản phẩm
    function slugify(text) {
        return text.toString().normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    // Tự động tạo slug khi nhập tên sản phẩm
    document.getElementById('name').addEventListener('input', function() {
        const nameValue = this.value;
        const slugValue = slugify(nameValue);
        document.getElementById('slug').value = slugValue;
    });

    // Xử lý nút "Tạo các biến thể mới"
    document.getElementById('generateVariants').addEventListener('click', function () {
        const colors = Array.from(document.querySelectorAll('#colorSelect option:checked'));
        const sizes = Array.from(document.querySelectorAll('#sizeSelect option:checked'));
        const container = document.getElementById('variantContainer');

        let currentIndex = container.children.length; // Bắt đầu từ index hiện tại của các biến thể đã có

        colors.forEach(color => {
            sizes.forEach(size => {
                // Kiểm tra xem biến thể này đã tồn tại chưa để tránh tạo trùng lặp khi nhấn lại nút
                const existingVariant = Array.from(container.children).find(card => {
                    const existingColorId = card.querySelector('input[name$="[color_id]"]').value;
                    const existingSizeId = card.querySelector('input[name$="[size_id]"]').value;
                    return existingColorId === color.value && existingSizeId === size.value;
                });

                if (!existingVariant) { // Chỉ tạo nếu biến thể chưa tồn tại
                    const variantId = `variant-${currentIndex}`;
                    const html = `
                        <div class="card p-3 mb-3 border shadow-sm" id="${variantId}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">${color.dataset.name} - ${size.dataset.name}</h5>
                                <div>
                                    <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-bs-target="#details-${variantId}">Sửa</button>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-target="#${variantId}">Xóa</button>
                                </div>
                            </div>
                            <input type="hidden" name="variants[${currentIndex}][color_id]" value="${color.value}">
                            <input type="hidden" name="variants[${currentIndex}][size_id]" value="${size.value}">
                            <div class="variant-details mt-2" id="details-${variantId}" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Mã SP</label>
                                        <input type="text" name="variants[${currentIndex}][sku]" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Kho</label>
                                        <select name="variants[${currentIndex}][stock]" class="form-select form-select-sm">
                                            <option value="0">Còn hàng</option>
                                            <option value="1">Hết hàng</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Giá</label>
                                        <input type="number" name="variants[${currentIndex}][price]" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Giá khuyến mãi</label>
                                        <input type="number" name="variants[${currentIndex}][sale_price]" class="form-control form-control-sm" id="sale_price_${currentIndex}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                        <input type="datetime-local" name="variants[${currentIndex}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_${currentIndex}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                        <input type="datetime-local" name="variants[${currentIndex}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_${currentIndex}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Ảnh</label>
                                        <div class="mb-2">
                                            <img class="preview-image mb-2 rounded" style="max-width: 100px; display: none;" src="" alt="Preview" id="img-preview-${currentIndex}">
                                        </div>
                                        <input type="file" name="variants[${currentIndex}][image]" class="form-control form-control-sm" onchange="document.getElementById('img-preview-${currentIndex}').style.display = 'block'; document.getElementById('img-preview-${currentIndex}').src = window.URL.createObjectURL(this.files[0])">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    currentIndex++;
                }
            });
        });

        // Gắn lại sự kiện cho các nút "Sửa" và "Xóa" mới được tạo
        attachVariantEventListeners();
    });

    // Hàm để gắn sự kiện cho các nút "Sửa" và "Xóa" của biến thể
    function attachVariantEventListeners() {
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.removeEventListener('click', toggleVariantDetails); // Loại bỏ sự kiện cũ để tránh trùng lặp
            button.addEventListener('click', toggleVariantDetails); // Gắn sự kiện mới
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.removeEventListener('click', deleteVariantCard); // Loại bỏ sự kiện cũ để tránh trùng lặp
            button.addEventListener('click', deleteVariantCard); // Gắn sự kiện mới
        });
    }

    // Hàm xử lý ẩn/hiện chi tiết biến thể
    function toggleVariantDetails() {
        const detailSection = document.querySelector(this.dataset.bsTarget);
        if (detailSection.style.display === 'none' || detailSection.style.display === '') {
            detailSection.style.display = 'block';
            this.textContent = 'Ẩn';
        } else {
            detailSection.style.display = 'none';
            this.textContent = 'Sửa';
        }
    }

    // Hàm xử lý xóa thẻ biến thể
    function deleteVariantCard() {
        const variantCard = document.querySelector(this.dataset.bsTarget);
        // Thêm một input hidden để đánh dấu biến thể này cần xóa khỏi DB
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `variants_to_delete[]`;
        // Tìm ID của biến thể nếu nó là biến thể hiện có (có ID)
        const variantIdInput = variantCard.querySelector('input[name$="[id]"]');
        if (variantIdInput) {
            hiddenInput.value = variantIdInput.value;
        } else {
            // Nếu không có ID, đây là biến thể mới được tạo trong phiên hiện tại, chỉ cần xóa khỏi DOM
            variantCard.remove();
            return;
        }
        document.querySelector('form').appendChild(hiddenInput); // Thêm vào form
        variantCard.remove(); // Xóa khỏi DOM
    }


    // Kích hoạt/vô hiệu hóa input ngày dựa trên giá khuyến mãi
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('[sale_price]')) {
            const index = e.target.name.match(/\[(\d+)\]/)[1];
            const startDateInput = document.getElementById(`sale_start_date_${index}`);
            const endDateInput = document.getElementById(`sale_end_date_${index}`);
            if (e.target.value.trim() !== '' && parseFloat(e.target.value) > 0) {
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

    // Khởi tạo các sự kiện khi DOM đã tải xong
    document.addEventListener('DOMContentLoaded', function() {
        attachVariantEventListeners(); // Gắn sự kiện cho các biến thể hiện có
        // Cập nhật trạng thái disabled của sale dates khi tải trang
        document.querySelectorAll('input[name$="[sale_price]"]').forEach(input => {
            const index = input.name.match(/\[(\d+)\]/)[1];
            const startDateInput = document.getElementById(`sale_start_date_${index}`);
            const endDateInput = document.getElementById(`sale_end_date_${index}`);
            if (input.value.trim() === '' || parseFloat(input.value) <= 0) {
                startDateInput.disabled = true;
                endDateInput.disabled = true;
            }
        });
    });
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    // Khởi tạo Select2 cho chọn màu sắc
    $('.select2-color').select2({
        placeholder: "Chọn màu sắc",
        allowClear: true
    });

    // Khởi tạo Select2 cho chọn kích cỡ
    $('.select2-size').select2({
        placeholder: "Chọn kích cỡ",
        allowClear: true
    });

    // Xử lý nút "Chọn tất cả" cho Select2
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