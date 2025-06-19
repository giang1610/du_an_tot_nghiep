@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/admin/products" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <h1 class="h4 mb-4">Thêm sản phẩm</h1>

    {{-- Hiển thị thông báo lỗi nếu có --}}
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
                    <div class="col-6">
                        <label for="short_description" class="form-label fw-medium">Mô tả ngắn</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description') }}">
                        @error('short_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Giá sản phẩm --}}
                    <div class="col-6">
                        <label for="price_products" class="form-label fw-medium">Giá sản phẩm</label>
                        <input type="number" name="price_products" id="price_products" class="form-control" placeholder="Nhập giá sản phẩm" value="{{ old('price_products') }}">
                        @error('price_products')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="thumbnail" class="form-label">Ảnh sản phẩm chính</label> <br>
                        {{-- Hiển thị ảnh cũ nếu có trong old('thumbnail_old') hoặc nếu có tệp mới được chọn trong old('thumbnail') --}}
                        <img id="img-main-preview" style="max-width: 100px; {{ old('thumbnail_old') || old('thumbnail') ? 'display: block;' : 'display: none;' }}" 
                             src="{{ old('thumbnail_old') ? asset('storage/' . old('thumbnail_old')) : (old('thumbnail') ? '#' : '') }}"> <br>
                        <input type="file" name="thumbnail" id="thumbnail" class="form-control" onchange="document.getElementById('img-main-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('img-main-preview').style.display = 'block';">
                        {{-- Trường hidden để giữ đường dẫn ảnh cũ khi validation thất bại --}}
                        <input type="hidden" name="thumbnail_old" value="{{ old('thumbnail_old') }}">
                        @error('thumbnail')
                        <div class="text-danger">{{$message}}</div>
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
                            <option value="">-- Chọn màu sắc --</option> {{-- Option rỗng cho placeholder của Select2 --}}
                            @foreach($colors as $color)
                            <option value="{{ $color->id }}" data-name="{{ $color->name }}" {{ in_array($color->id, old('color_id', [])) ? 'selected' : '' }}>{{ $color->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target="#colorSelect">Chọn tất cả màu</button>
                        @error('color_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="sizeSelect" class="form-label fw-medium">Kích cỡ</label>
                        <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
                            <option value="">-- Chọn kích cỡ --</option> {{-- Option rỗng cho placeholder của Select2 --}}
                            @foreach($sizes as $size)
                            <option value="{{ $size->id }}" data-name="{{ $size->name }}" {{ in_array($size->id, old('size_id', [])) ? 'selected' : '' }}>{{ $size->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target="#sizeSelect">Chọn tất cả size</button>
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

        <div id="variantContainer">
            {{-- Hiển thị các biến thể đã tồn tại (do validation thất bại) --}}
            @if(old('variants'))
                @foreach(old('variants') as $i => $variant)
                    @php
                        // Kiểm tra xem có lỗi validation nào cho biến thể này không
                        // Sử dụng $errors->has() trực tiếp để kiểm tra lỗi từ ViewErrorBag
                        $showVariantDetails = $errors->has('variants.' . $i . '.*');
                    @endphp
                    <div class="card p-3 mb-3 border shadow-sm" id="variant-{{ $i }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                @php
                                    $colorId = is_array($variant) ? ($variant['color_id'] ?? null) : null;
                                    $sizeId = is_array($variant) ? ($variant['size_id'] ?? null) : null;
                                @endphp
                                {{ optional($colors->firstWhere('id', $colorId))->name ?? '' }} -
                                {{ optional($sizes->firstWhere('id', $sizeId))->name ?? '' }}
                            </h5>
                            <div>
                                {{-- Nút Sửa/Ẩn: Văn bản nút và trạng thái hiển thị phải khớp --}}
                                <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-bs-target="#details-variant-{{ $i }}">
                                    {{ $showVariantDetails ? 'Ẩn' : 'Sửa' }}
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-target="#variant-{{ $i }}">Xóa</button>
                            </div>
                        </div>
                        <input type="hidden" name="variants[{{ $i }}][color_id]" value="{{ is_array($variant) ? ($variant['color_id'] ?? '') : '' }}">
                        <input type="hidden" name="variants[{{ $i }}][size_id]" value="{{ is_array($variant) ? ($variant['size_id'] ?? '') : '' }}">
                        {{-- Hiển thị chi tiết biến thể nếu có lỗi validation --}}
                        <div class="variant-details mt-2" id="details-variant-{{ $i }}" style="display: {{ $showVariantDetails ? 'block' : 'none' }};">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Mã SP</label>
                                    <input type="text" name="variants[{{ $i }}][sku]" class="form-control form-control-sm" value="{{ old('variants.'.$i.'.sku', $variant['sku'] ?? '') }}">
                                    @error('variants.'.$i.'.sku')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                <div class="col-md-6">
                                    <label class="form-label small">Giá</label>
                                    <input type="number" name="variants[{{ $i }}][price]" class="form-control form-control-sm" value="{{ old('variants.'.$i.'.price', $variant['price'] ?? '') }}">
                                    @error('variants.'.$i.'.price')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Giá khuyến mãi</label>
                                    <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control form-control-sm" id="sale_price_{{ $i }}" value="{{ old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '') }}">
                                    @error('variants.'.$i.'.sale_price')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                    <input type="datetime-local" name="variants[{{ $i }}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_{{ $i }}" value="{{ old('variants.'.$i.'.sale_start_date', $variant['sale_start_date'] ?? '') }}">
                                    @error('variants.'.$i.'.sale_start_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                    <input type="datetime-local" name="variants[{{ $i }}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_{{ $i }}" value="{{ old('variants.'.$i.'.sale_end_date', $variant['sale_end_date'] ?? '') }}">
                                    @error('variants.'.$i.'.sale_end_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Ảnh biến thể</label>
                                    <div class="mb-2">
                                        {{-- Hiển thị ảnh cũ của biến thể nếu có trong old('variants.'.$i.'.existing_image') --}}
                                        <img class="preview-image-variant mb-2 rounded" style="max-width: 100px; {{ old('variants.'.$i.'.existing_image') ? 'display: block;' : 'display: none;' }}"
                                             src="{{ old('variants.'.$i.'.existing_image') ? asset('storage/' . old('variants.'.$i.'.existing_image')) : '' }}"
                                             alt="Preview" id="img-preview-{{ $i }}">
                                    </div>
                                    <input type="file" name="variants[{{ $i }}][image]" class="form-control form-control-sm" onchange="document.getElementById('img-preview-{{ $i }}').src = window.URL.createObjectURL(this.files[0]); document.getElementById('img-preview-{{ $i }}').style.display = 'block';">
                                    {{-- Trường hidden để giữ đường dẫn ảnh cũ của biến thể khi validation thất bại --}}
                                    <input type="hidden" name="variants[{{ $i }}][existing_image]" value="{{ old('variants.'.$i.'.existing_image') }}">
                                    @error('variants.'.$i.'.image')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="mt-4 d-flex justify-content-start">
            <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
        </div>
    </form>
</div>

{{-- Các thư viện JS và CSS cho Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- KHỞI TẠO VÀ XỬ LÝ SELECT2 ---
    $(document).ready(function () {
        // Khởi tạo Select2 cho cả màu sắc và kích cỡ
        $('.select2-color').select2({
            placeholder: "-- Chọn màu sắc --", // Đặt placeholder
            allowClear: true // Cho phép xóa lựa chọn
        });

        $('.select2-size').select2({
            placeholder: "-- Chọn kích cỡ --", // Đặt placeholder
            allowClear: true // Cho phép xóa lựa chọn
        });

        // Xử lý nút "Chọn tất cả"
        $('.select-all').on('click', function () {
            const targetId = $(this).data('target'); // Lấy ID của select, ví dụ: "#colorSelect"
            const selectElement = $(targetId); // Chọn phần tử select bằng ID

            const allValues = [];
            selectElement.find('option').each(function () {
                // Đảm bảo không thêm option rỗng vào danh sách chọn tất cả
                if ($(this).val() !== '') { 
                    allValues.push($(this).val());
                }
            });
            // Đặt giá trị và kích hoạt sự kiện 'change' để Select2 cập nhật giao diện
            selectElement.val(allValues).trigger('change');
        });

        // QUAN TRỌNG: Kích hoạt sự kiện 'change' cho Select2 khi trang tải với dữ liệu old()
        // Điều này giúp Select2 hiển thị các lựa chọn đã được chọn từ lần gửi form trước đó
        $('#colorSelect').trigger('change');
        $('#sizeSelect').trigger('change');
    });

    // --- CÁC CHỨC NĂNG KHÁC ---

    // Chức năng tạo slug tự động từ tên sản phẩm
    function slugify(text) {
        return text.toString().normalize('NFD')
            .replace(/đ/g, 'd') // chuyển đ thành d
            .replace(/Đ/g, 'D') // chuyển Đ thành D
            .replace(/[\u0300-\u036f]/g, '') // loại bỏ dấu
            .toLowerCase() // chuyển thành chữ thường
            .trim() // xóa khoảng trắng đầu cuối
            .replace(/[^a-z0-9\s-]/g, '') // thay thế ký tự không phải chữ, số, khoảng trắng, gạch ngang
            .replace(/\s+/g, '-') // thay thế khoảng trắng bằng gạch ngang
            .replace(/-+/g, '-'); // thay thế nhiều gạch ngang liên tiếp bằng một gạch ngang
    }

    // Gắn sự kiện để tự động tạo slug khi tên sản phẩm thay đổi
    document.getElementById('name').addEventListener('input', function() {
        const nameValue = this.value;
        const slugValue = slugify(nameValue);
        document.getElementById('slug').value = slugValue;
    });

    // Xử lý preview ảnh chính sản phẩm
    document.getElementById('thumbnail').addEventListener('change', function(event) {
        const img = document.getElementById('img-main-preview');
        if (event.target.files && event.target.files[0]) {
            img.src = URL.createObjectURL(event.target.files[0]);
            img.style.display = 'block';
        } else {
            img.src = '';
            img.style.display = 'none';
        }
    });

    // Thiết lập ban đầu cho các trường ngày khuyến mãi của biến thể đã tồn tại (từ dữ liệu old())
    // Đảm bảo các trường ngày bị vô hiệu hóa nếu không có giá khuyến mãi
    document.querySelectorAll('.variant-details').forEach(detailSection => {
        const salePriceInput = detailSection.querySelector('input[name$="[sale_price]"]');
        if (salePriceInput) {
            const index = salePriceInput.id.split('_')[1];
            const startDateInput = document.getElementById(`sale_start_date_${index}`);
            const endDateInput = document.getElementById(`sale_end_date_${index}`);

            if (salePriceInput.value.trim() !== '' && parseFloat(salePriceInput.value.trim()) > 0) {
                startDateInput.disabled = false;
                endDateInput.disabled = false;
            } else {
                startDateInput.disabled = true;
                endDateInput.disabled = true;
                // Chỉ xóa giá trị ngày nếu giá khuyến mãi rỗng
                if (!salePriceInput.value.trim()) { 
                   startDateInput.value = '';
                   endDateInput.value = '';
                }
            }
        }
    });

    // Xử lý khi nhấn nút "Tạo các biến thể"
    document.getElementById('generateVariants').addEventListener('click', function () {
        const colors = Array.from(document.querySelectorAll('#colorSelect option:checked'));
        const sizes = Array.from(document.querySelectorAll('#sizeSelect option:checked'));
        const container = document.getElementById('variantContainer');
        container.innerHTML = ''; // Xóa các biến thể đã tạo trước đó

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
                                    <label class="form-label small">Mã SP</label>
                                    <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Kho</label>
                                    <select name="variants[${index}][stock]" class="form-select form-select-sm">
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
                                        <img class="preview-image-variant mb-2 rounded" style="max-width: 100px; display: none;" src="" alt="Preview" id="img-preview-${index}">
                                    </div>
                                    <input type="file" name="variants[${index}][image]" class="form-control form-control-sm" onchange="document.getElementById('img-preview-${index}').src = window.URL.createObjectURL(this.files[0]); document.getElementById('img-preview-${index}').style.display = 'block';">
                                    <input type="hidden" name="variants[${index}][existing_image]" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
                index++;
            });
        });

        // Gắn lại các sự kiện cho các biến thể mới được tạo
        attachVariantEventListeners();
    });

    // Hàm để gắn/gắn lại các sự kiện cho biến thể (Sửa/Xóa, bật/tắt ngày khuyến mãi)
    function attachVariantEventListeners() {
        // Sự kiện cho nút "Sửa" để bật/tắt chi tiết biến thể
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.removeEventListener('click', toggleVariantDetails); // Xóa listener cũ để tránh trùng lặp
            button.addEventListener('click', toggleVariantDetails);
        });

        // Sự kiện cho nút "Xóa" để xóa biến thể
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.removeEventListener('click', deleteVariantCard); // Xóa listener cũ
            button.addEventListener('click', deleteVariantCard);
        });

        // Sự kiện để bật/tắt các trường ngày khuyến mãi dựa trên giá khuyến mãi
        document.querySelectorAll('input[name$="[sale_price]"]').forEach(input => {
            input.removeEventListener('input', toggleSaleDateInputs); // Xóa listener cũ
            input.addEventListener('input', toggleSaleDateInputs);
        });
    }

    // Hàm bật/tắt chi tiết biến thể
    function toggleVariantDetails() {
        const detailSection = document.querySelector(this.dataset.bsTarget);
        if (detailSection.style.display === 'none') {
            detailSection.style.display = 'block';
            this.textContent = 'Ẩn'; // Đổi text thành 'Ẩn' khi hiển thị
        } else {
            detailSection.style.display = 'none';
            this.textContent = 'Sửa'; // Đổi text thành 'Sửa' khi ẩn
        }
    }

    // Hàm xóa thẻ biến thể
    function deleteVariantCard() {
        const variantCard = document.querySelector(this.dataset.bsTarget);
        variantCard.remove();
    }

    // Hàm bật/tắt các trường nhập ngày khuyến mãi dựa trên giá khuyến mãi
    function toggleSaleDateInputs(e) {
        const index = e.target.name.match(/\[(\d+)\]/)[1];
        const startDateInput = document.getElementById(`sale_start_date_${index}`);
        const endDateInput = document.getElementById(`sale_end_date_${index}`);
        if (e.target.value.trim() !== '' && parseFloat(e.target.value.trim()) > 0) {
            startDateInput.disabled = false;
            endDateInput.disabled = false;
        } else {
            startDateInput.disabled = true;
            endDateInput.disabled = true;
            startDateInput.value = '';
            endDateInput.value = '';
        }
    }

    // Gọi hàm attachVariantEventListeners một lần khi tải trang để xử lý các biến thể từ old()
    attachVariantEventListeners();
});
</script>
@endsection