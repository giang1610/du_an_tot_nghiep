@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <a href="/admin/products" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
                <h5 class="mb-0">Thêm sản phẩm mới</h5>
            </div>
        </div>

        <div class="card-body">
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-5">
                    <h6 class="mb-3 text-primary fw-semibold border-bottom pb-2">Thông tin cơ bản</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên sản phẩm" value="{{ old('name') }}" >
                            @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="slug" class="form-label">Đường dẫn</label>
                            <div class="input-group">
                                <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn tự động tạo" value="{{ old('slug') }}">
                            </div>
                            @error('slug')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Mô tả sản phẩm</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả sản phẩm" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="short_description" class="form-label">Mô tả ngắn</label>
                            <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description') }}">
                            @error('short_description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="price_products" class="form-label">Giá sản phẩm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">VNĐ</span>
                                <input type="number" name="price_products" id="price_products" class="form-control" placeholder="Nhập giá sản phẩm" value="{{ old('price_products') }}" >
                            </div>
                            @error('price_products')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Media & Category Section -->
                <div class="mb-5">
                    <h6 class="mb-3 text-primary fw-semibold border-bottom pb-2">Hình ảnh & Danh mục</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="thumbnail" class="form-label">Ảnh sản phẩm chính <span class="text-danger">*</span></label>
                            <div class="image-upload-card border rounded p-3">
                                <div class="image-preview-wrapper text-center mb-2" onclick="document.getElementById('thumbnail').click()">
                                    <img id="img-main-preview" class="img-fluid rounded border" 
                                        style="max-height: 180px; display: none;" 
                                        src="">
                                    <div class="upload-placeholder" id="upload-placeholder">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Click để chọn ảnh</p>
                                    </div>
                                    <input type="file" name="thumbnail" id="thumbnail" class="d-none">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2" id="remove-main-image" style="display: none;">
                                    <i class="bi bi-trash me-1"></i> Chọn ảnh khác
                                </button>
                                <input type="hidden" name="thumbnail_old" value="{{ old('thumbnail_old') }}">
                            </div>
                            @error('thumbnail')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" >
                                        <option value="">-- Chọn danh mục --</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Trạng thái</span></label>
                                    <select name="status" class="form-select" >
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
                </div>

                <!-- Variant Attributes Section -->
                <div class="mb-5">
                    <h6 class="mb-3 text-primary fw-semibold border-bottom pb-2">Thuộc tính biến thể</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="colorSelect" class="form-label">Màu sắc</label>
                            <select name="color_id[]" class="form-select select2-color" multiple id="colorSelect">
                                <option value="">-- Chọn màu sắc --</option>
                                @foreach($colors as $color)
                                <option value="{{ $color->id }}" data-name="{{ $color->name }}" {{ in_array($color->id, old('color_id', [])) ? 'selected' : '' }}>{{ $color->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2 select-all" data-target="#colorSelect">
                                <i class="bi bi-check-all me-1"></i> Chọn tất cả màu
                            </button>
                            @error('color_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sizeSelect" class="form-label">Kích cỡ</label>
                            <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
                                @foreach($sizes as $size)
                                <option value="{{ $size->id }}" data-name="{{ $size->name }}" {{ in_array($size->id, old('size_id', [])) ? 'selected' : '' }}>{{ $size->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2 select-all" data-target="#sizeSelect">
                                <i class="bi bi-check-all me-1"></i> Chọn tất cả size
                            </button>
                            @error('size_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary px-4" id="generateVariants">
                            Tạo biến thể
                        </button>
                        <input type="hidden" name="has_variants" value="1">
                    </div>
                </div>

                <!-- Variants Container -->
                <div id="variantContainer" class="mb-4">
                    @if(old('variants'))
                        @foreach(old('variants') as $i => $variant)
                            @php
                                $showVariantDetails = $errors->has('variants.' . $i . '.*');
                                $colorId = is_array($variant) ? ($variant['color_id'] ?? null) : null;
                                $sizeId = is_array($variant) ? ($variant['size_id'] ?? null) : null;
                                $colorName = optional($colors->firstWhere('id', $colorId))->name ?? '';
                                $sizeName = optional($sizes->firstWhere('id', $sizeId))->name ?? '';
                            @endphp
                            <div class="card mb-3 border" id="variant-{{ $i }}">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            @if($colorName)
                                            <span class="badge bg-primary me-2">{{ $colorName }}</span>
                                            @endif
                                            @if($sizeName)
                                            <span class="badge bg-secondary">{{ $sizeName }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            {{-- Nút Sửa/Ẩn: Văn bản nút và trạng thái hiển thị phải khớ --}}
                                            <button type="button" class="btn btn-sm btn-outline-warning me-1 btn-edit" data-bs-target="#details-variant-{{ $i }}">
                                                <i class="bi {{ $showVariantDetails ? 'bi-eye-slash' : 'bi-pencil' }} me-1"></i> {{ $showVariantDetails ? 'Ẩn' : 'Sửa' }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-target="#variant-{{ $i }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Các trường ẩn để lưu trữ thông tin biến thể --}}
                                <input type="hidden" name="variants[{{ $i }}][color_id]" value="{{ $variant['color_id'] ?? '' }}">
                                <input type="hidden" name="variants[{{ $i }}][size_id]" value="{{ $variant['size_id'] ?? '' }}">

                                {{-- Phần thân của thẻ card chứa các trường thông tin biến thể --}}
                                <div class="card-body variant-details" id="details-variant-{{ $i }}" style="display: {{ $showVariantDetails ? 'block' : 'none' }};">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small">Mã SP</label>
                                            <input type="text" name="variants[{{ $i }}][sku]" class="form-control form-control-sm" value="{{ old('variants.'.$i.'.sku', $variant['sku'] ?? '') }}">
                                            @error('variants.'.$i.'.sku')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Tình trạng kho</label>
                                            <select name="variants[{{ $i }}][stock_status]" class="form-select form-select-sm stock-status" data-index="{{ $i }}">
                                                {{-- <option value="" {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '') == '' ? 'selected' : '' }}>Trạng thái kho</option>
                                                <option value="0" {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '') == '0' ? 'selected' : '' }}>Hết hàng</option> --}}
                                                <option value="1" {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '') == '1' ? 'selected' : '' }}>Còn hàng</option>
                                            </select>
                                            <input type="number" name="variants[{{ $i }}][stock_quantity]" class="form-control form-control-sm mt-2 stock-quantity"
                                                style="display: {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '') == '1' ? 'block' : 'none' }};"
                                                value="{{ old('variants.'.$i.'.stock_quantity', $variant['stock_quantity'] ?? '') }}" placeholder="Nhập số lượng">
                                            @error('variants.'.$i.'.stock_quantity')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                            @error('variants.'.$i.'.stock_status')
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
                                            <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control form-control-sm" id="sale_price_{{ $i }}" 
                                                value="{{ old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '') }}">
                                            @error('variants.'.$i.'.sale_price')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Ngày bắt đầu KM</label>
                                            <input type="datetime-local" name="variants[{{ $i }}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_{{ $i }}" 
                                                value="{{ old('variants.'.$i.'.sale_start_date', $variant['sale_start_date'] ?? '') }}"
                                                {{ !old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '') ? 'disabled' : '' }}>
                                            @error('variants.'.$i.'.sale_start_date')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Ngày kết thúc KM</label>
                                            <input type="datetime-local" name="variants[{{ $i }}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_{{ $i }}" 
                                                value="{{ old('variants.'.$i.'.sale_end_date', $variant['sale_end_date'] ?? '') }}"
                                                {{ !old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '') ? 'disabled' : '' }}>
                                            @error('variants.'.$i.'.sale_end_date')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Ảnh biến thể</label>
                                            <div class="image-upload-card border rounded p-2">
                                                <div class="image-preview-wrapper-sm text-center" onclick="this.querySelector('input[type=file]').click()">
                                                    <img class="img-fluid rounded preview-image-variant" 
                                                        style="max-height: 100px; {{ old('variants.'.$i.'.existing_image') ? '' : 'display: none;' }}"
                                                        src="{{ old('variants.'.$i.'.existing_image') ? asset('storage/' . old('variants.'.$i.'.existing_image')) : '' }}"
                                                        alt="Preview" id="img-preview-{{ $i }}">
                                                    <div class="upload-placeholder-sm {{ old('variants.'.$i.'.existing_image') ? 'd-none' : '' }}">
                                                        <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                                        <p class="text-muted mt-1 mb-0 small">Click để chọn ảnh</p>
                                                    </div>
                                                    <input type="file" name="variants[{{ $i }}][image]" class="d-none">
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2 btn-remove-variant-image" 
                                                        data-target="img-preview-{{ $i }}" 
                                                        style="{{ old('variants.'.$i.'.existing_image') ? '' : 'display: none;' }}">
                                                    <i class="bi bi-trash me-1"></i> Xóa ảnh
                                                </button>
                                                <input type="hidden" name="variants[{{ $i }}][existing_image]" value="{{ old('variants.'.$i.'.existing_image') }}">
                                            </div>
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

                <!-- Form Actions -->
                <div class="d-flex justify-content-end border-top pt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i> Lưu sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .image-upload-card {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }
    .image-upload-wrapper {
        position: relative;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        padding: 1rem;
    }
    .upload-area {
        padding: 2rem;
        cursor: pointer;
    }
    .image-preview-container {
        position: relative;
    }
    .image-preview-wrapper-sm .image-preview-wrapper-lg {
        position: relative;
        width: 80px;
        min-height: 100px;
        align-items: center;
        justify-content: center;
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
        overflow: hidden;
        cursor: pointer;
    }
    .image-upload-overlay-sm {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0,0,0,0.05);
        color: #6c757d;
    }
    .image-preview-wrapper-sm:hover .image-upload-overlay-sm {
        background-color: rgba(0,0,0,0.1);
    }
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .card-header {
        background-color: #f8f9fa;
    }
    .border-bottom {
        border-bottom: 1px solid #e9ecef !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- ORIGINAL JAVASCRIPT LOGIC (KEPT UNCHANGED) -->
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
    //xử lý khi chọn còn hàng thì hiện ô nhập số lượng
        $(document).on('change', '.stock-status', function() {
        var index = $(this).data('index');
        var $qty = $(this).closest('.row').find('.stock-quantity');
        if ($(this).val() == '1') {
            $qty.show();
        } else {
            $qty.hide().val(0);
        }
    });
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

    // Xử lý sự kiện khi người dùng chọn ảnh chính
    document.getElementById('thumbnail').addEventListener('change', function(e) {
        const preview = document.getElementById('img-main-preview');
        const placeholder = document.getElementById('upload-placeholder');
        
        if (e.target.files && e.target.files[0]) {
            preview.src = URL.createObjectURL(e.target.files[0]);
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
    });

    // Xử lý nút xóa ảnh chính
    const thumbnailInput = document.getElementById('thumbnail');
    const preview = document.getElementById('img-main-preview');
    const placeholder = document.getElementById('upload-placeholder');
    const removeBtn = document.getElementById('remove-main-image');
    const thumbnailOld = document.querySelector('input[name="thumbnail_old"]');

    // Xử lý khi chọn ảnh
    thumbnailInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            preview.src = URL.createObjectURL(e.target.files[0]);
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            removeBtn.style.display = 'block';
        }
    });

    // Xử lý nút xóa ảnh chính
    removeBtn?.addEventListener('click', function() {
        preview.src = '';
        preview.style.display = 'none';
        thumbnailInput.value = '';
        placeholder.style.display = 'block';
        this.style.display = 'none';
        thumbnailOld.value = '';
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
                    <div class="card mb-3 border" id="${variantId}">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center mt-2">
                                    <h5 class="badge bg-primary me-2">${color.dataset.name}</h5>
                                    <h5 class="badge bg-secondary">${size.dataset.name}</h5>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-warning me-1 btn-edit" data-bs-target="#details-${variantId}">
                                        <i class="bi bi-pencil me-1"></i> Sửa
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-bs-target="#${variantId}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="variants[${index}][color_id]" value="${color.value}">
                        <input type="hidden" name="variants[${index}][size_id]" value="${size.value}">
                        <div class="card-body variant-details" id="details-${variantId}" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Mã SP</label>
                                    <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Kho</label>
                                    <select name="variants[${index}][stock_status]" class="form-select form-select-sm stock-status" data-index="${index}">
                                        <option value="">trạng thái kho</option>
                                        <option value="0">Hết hàng</option>
                                        <option value="1">Còn hàng</option>
                                    </select>
                                    <input type="number" name="variants[${index}][stock_quantity]" class="form-control form-control-sm mt-2 stock-quantity" style="display:none;" min="0" placeholder="Nhập số lượng">
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
                                    <label class="form-label small">Ngày bắt đầu KM</label>
                                    <input type="datetime-local" name="variants[${index}][sale_start_date]" class="form-control form-control-sm" id="sale_start_date_${index}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Ngày kết thúc KM</label>
                                    <input type="datetime-local" name="variants[${index}][sale_end_date]" class="form-control form-control-sm" id="sale_end_date_${index}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Ảnh biến thể</label>
                                    <div class="image-upload-card border rounded p-3">
                                        <div class="image-preview-wrapper-lg text-center" onclick="this.querySelector('input[type=file]').click()">
                                            <img class="img-fluid preview-image-variant" 
                                                style="max-height: 100px; display: none;"
                                                src="" 
                                                alt="Preview" 
                                                id="img-preview-${index}">
                                            <div class="upload-placeholder-sm">
                                                <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                                <p class="text-muted mt-1 mb-0 small">Click để chọn ảnh</p>
                                            </div>
                                            <input type="file" name="variants[${index}][image]" class="d-none">
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2 btn-remove-variant-image" 
                                                data-target="img-preview-${index}" 
                                                style="display: none;">
                                            <i class="bi bi-trash me-1"></i> Xóa ảnh
                                        </button>
                                        <input type="hidden" name="variants[${index}][existing_image]" value="">
                                    </div>
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
        // Xử lý thống nhất cho ảnh biến thể
        document.querySelectorAll('.image-preview-wrapper-lg').forEach(wrapper => {
            const fileInput = wrapper.querySelector('input[type="file"]');
            const preview = wrapper.querySelector('img');
            const placeholder = wrapper.querySelector('.upload-placeholder-sm');
            const previewId = preview.id;
            const removeBtn = document.querySelector(`.btn-remove-variant-image[data-target="${previewId}"]`);
            const hiddenInput = wrapper.closest('.image-upload-card').querySelector('input[type="hidden"]');

            // Biến cờ để kiểm soát sự kiện
            let isProcessing = false;

            // Xử lý khi click vào vùng preview
            wrapper.addEventListener('click', function(e) {
                if (e.target === wrapper || e.target === placeholder) {
                    if (!isProcessing) {
                        isProcessing = true;
                        fileInput.click();
                    }
                }
            }, { once: true }); // Sử dụng once: true để chỉ gọi 1 lần

            // Xử lý khi chọn file ảnh
            fileInput.addEventListener('change', function(e) {
                isProcessing = false; // Reset cờ khi đã xử lý xong
                
                if (e.target.files && e.target.files[0]) {
                    preview.src = URL.createObjectURL(e.target.files[0]);
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (removeBtn) removeBtn.style.display = 'block';
                    
                    // Thêm lại sự kiện click sau khi xử lý xong
                    wrapper.addEventListener('click', handleWrapperClick, { once: true });
                }
            });

            // Hàm xử lý click wrapper
            function handleWrapperClick(e) {
                if (e.target === wrapper || e.target === placeholder) {
                    if (!isProcessing) {
                        isProcessing = true;
                        fileInput.click();
                    }
                }
            }

            // Xử lý nút xóa ảnh
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    preview.src = '';
                    preview.style.display = 'none';
                    fileInput.value = '';
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                    this.style.display = 'none';
                    if (hiddenInput) hiddenInput.value = '';
                    
                    // Thêm lại sự kiện click sau khi xóa ảnh
                    wrapper.addEventListener('click', handleWrapperClick, { once: true });
                });
            }

            // Gán sự kiện ban đầu
            wrapper.addEventListener('click', handleWrapperClick, { once: true });
        });
    }

    // Hàm bật/tắt chi tiết biến thể
    function toggleVariantDetails() {
        const detailSection = document.querySelector(this.dataset.bsTarget);
        if (detailSection.style.display === 'none') {
            detailSection.style.display = 'block';
            this.innerHTML = '<i class="bi bi-eye-slash me-1"></i> Ẩn';
        } else {
            detailSection.style.display = 'none';
            this.innerHTML = '<i class="bi bi-pencil me-1"></i> Sửa';
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