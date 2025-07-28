@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/admin/products" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <h1 class="h4 mb-4">Chỉnh sửa sản phẩm</h1>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('products.update', $product->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-medium">Tên sản phẩm</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên sản phẩm" value="{{ old('name', $product->name) }}">
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-medium">Đường dẫn</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn tự động tạo" value="{{ old('slug', $product->slug) }}">
                        @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label fw-medium">Mô tả sản phẩm</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả sản phẩm" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-6">
                        <label for="short_description" class="form-label fw-medium">Mô tả ngắn</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description', $product->short_description) }}">
                        @error('short_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-6">
                        <label for="price_products" class="form-label fw-medium">Giá sản phẩm</label>
                        <input type="number" name="price_products" id="price_products" class="form-control" placeholder="Nhập giá sản phẩm" value="{{ old('price_products', (int)$product->price_products) }}">
                        @error('price_products')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="thumbnail" class="form-label">Ảnh sản phẩm chính</label>
                        <div class="mb-2">
                            <img id="img-main-preview" style="max-width: 100px; {{ $product->thumbnail ? 'display: block;' : 'display: none;' }}" 
                                 src="{{ $product->thumbnail ? asset('storage/'.$product->thumbnail) : '' }}">
                        </div>
                        <input type="file" name="thumbnail" id="thumbnail" class="form-control">
                        <input type="hidden" name="thumbnail_old" value="{{ $product->thumbnail }}">
                        @error('thumbnail')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-medium">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
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
                    
                    <div class="col-md-6">
                        <label for="sizeSelect" class="form-label fw-medium">Kích cỡ</label>
                        <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
                            @foreach($sizes as $size)
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
            <button type="button" class="btn btn-primary" id="generateVariants">Tạo các biến thể mới</button>
            <input type="hidden" name="has_variants" value="1">
        </div>

        <div id="variantContainer">
            @php
                $variantsToDisplay = empty(old('variants')) ? $product->variants->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'sale_start_date' => $variant->sale_start_date,
                        'sale_end_date' => $variant->sale_end_date,
                        'color_id' => $variant->color_id,
                        'size_id' => $variant->size_id,
                        'image' => $variant->image,
                        'stock_quantity' => $variant->stock ? $variant->stock->quantity : 0,
                        'stock_status' => $variant->stock && $variant->stock->quantity > 0 ? '1' : '0'
                    ];
                })->toArray() : old('variants');
                $nextVariantIndex = count($variantsToDisplay);
            @endphp
            
            @foreach($variantsToDisplay as $i => $variant)
                <div class="card p-3 mb-3 border shadow-sm" id="variant-{{ $i }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">
                            {{ $colors->firstWhere('id', $variant['color_id'])?->name ?? 'Màu không xác định' }} -
                            {{ $sizes->firstWhere('id', $variant['size_id'])?->name ?? 'Kích cỡ không xác định' }}
                        </h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" data-bs-target="#details-variant-{{ $i }}">
                                Ẩn
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-bs-target="#variant-{{ $i }}">Xóa</button>
                        </div>
                    </div>
                    
                    @if (isset($variant['id']))
                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] }}">
                    @endif
                    <input type="hidden" name="variants[{{ $i }}][color_id]" value="{{ $variant['color_id'] }}">
                    <input type="hidden" name="variants[{{ $i }}][size_id]" value="{{ $variant['size_id'] }}">
                    
                    <div class="variant-details mt-2" id="details-variant-{{ $i }}" style="display: block;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Mã SP</label>
                                <input type="text" name="variants[{{ $i }}][sku]" class="form-control form-control-sm" 
                                       value="{{ old('variants.'.$i.'.sku', $variant['sku'] ?? '') }}">
                                @error('variants.'.$i.'.sku')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Trạng thái Kho</label>
                                <select name="variants[{{ $i }}][stock_status]" class="form-select form-select-sm stock-status" data-index="{{ $i }}">
                                    <option value="" {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '') == '' ? 'selected' : '' }}>-- Chọn trạng thái kho --</option>
                                    <option value="0" {{ (string)old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '0') == 0 ? 'selected' : '' }}>Hết hàng</option>
                                    <option value="1" {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '0') == 1 ? 'selected' : '' }}>Còn hàng</option>
                                  

                                   
                                </select><br>
                                <input type="number" name="variants[{{ $i }}][stock_quantity]" class="form-control form-control-sm stock-quantity"
                                    style="display: {{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '0') == 1 ? 'block' : 'none' }};"
                                    value="{{ old('variants.'.$i.'.stock_quantity', $variant['stock_quantity'] ?? '') }}" min="0" placeholder="Nhập số lượng">
                                    @error('variants.'.$i.'.stock_quantity')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Giá</label>
                                <input type="number" name="variants[{{ $i }}][price]" class="form-control form-control-sm" 
                                       value="{{ old('variants.'.$i.'.price', (int)$variant['price'] ?? '') }}">
                                @error('variants.'.$i.'.price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Giá khuyến mãi</label>
                                <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control form-control-sm" 
                                       id="sale_price_{{ $i }}" value="{{ old('variants.'.$i.'.sale_price', isset($variant['sale_price']) && $variant['sale_price'] !== null ? (is_numeric($variant['sale_price']) ? rtrim(rtrim($variant['sale_price'], '0'), '.') : $variant['sale_price']) : '') }}">
                                @error('variants.'.$i.'.sale_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                <input type="datetime-local" name="variants[{{ $i }}][sale_start_date]" 
                                       class="form-control form-control-sm" id="sale_start_date_{{ $i }}" 
                                       value="{{ old('variants.'.$i.'.sale_start_date', isset($variant['sale_start_date']) ? \Carbon\Carbon::parse($variant['sale_start_date'])->format('Y-m-d\TH:i') : '') }}"
                                       {{ empty(old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '')) ? 'disabled' : '' }}>
                                @error('variants.'.$i.'.sale_start_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                <input type="datetime-local" name="variants[{{ $i }}][sale_end_date]" 
                                       class="form-control form-control-sm" id="sale_end_date_{{ $i }}" 
                                       value="{{ old('variants.'.$i.'.sale_end_date', isset($variant['sale_end_date']) ? \Carbon\Carbon::parse($variant['sale_end_date'])->format('Y-m-d\TH:i') : '') }}"
                                       {{ empty(old('variants.'.$i.'.sale_price', $variant['sale_price'] ?? '')) ? 'disabled' : '' }}>
                                @error('variants.'.$i.'.sale_end_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small">Ảnh biến thể</label>
                                <div class="mb-2">
                                    <img class="preview-image-variant mb-2 rounded"
                                         style="max-width: 100px; {{ isset($variant['image']) ? 'display: block;' : 'display: none;' }}"
                                         src="{{ isset($variant['image']) ? asset('storage/'.$variant['image']) : '' }}"
                                         alt="Preview" id="img-preview-{{ $i }}">
                                </div>
                                <input type="file" name="variants[{{ $i }}][image]" class="form-control form-control-sm"
                                       onchange="document.getElementById('img-preview-{{ $i }}').style.display = 'block'; document.getElementById('img-preview-{{ $i }}').src = window.URL.createObjectURL(this.files[0])">
                                <input type="hidden" name="variants[{{ $i }}][image_old]" value="{{ $variant['image'] ?? '' }}">
                                <small class="text-muted mt-1">Để thay đổi ảnh, hãy chọn tệp mới. Ảnh hiện tại sẽ được giữ nếu không chọn.</small>
                                @error('variants.'.$i.'.image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 product-update-button-container">
            <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Khởi tạo Select2
    $('.select2-color, .select2-size').select2({
        placeholder: "Chọn tùy chọn",
        allowClear: true
    });

    // Nút chọn tất cả
    $('.select-all').click(function() {
        const target = $(this).data('target');
        $(target).find('option').prop('selected', true);
        $(target).trigger('change');
    });

    // Tự động tạo slug
    $('#name').on('input', function() {
        $('#slug').val(slugify($(this).val()));
    });

    // Xử lý trạng thái kho
    $(document).on('change', '.stock-status', function() {
        const $row = $(this).closest('.row');
        const $qty = $row.find('.stock-quantity');
        $qty.toggle($(this).val() === '1');
        if ($(this).val() !== '1') {
            $qty.val(0);
        }
    });

    // Kích hoạt sự kiện change ban đầu
    $('.stock-status').trigger('change');

    // Xử lý nút "Tạo các biến thể mới"
    $('#generateVariants').click(function() {
        const colors = $('#colorSelect option:selected');
        const sizes = $('#sizeSelect option:selected');
        const container = $('#variantContainer');
        let currentIndex = {{ $nextVariantIndex }};
        colors.each(function() {
            const color = $(this);
            sizes.each(function() {
                const size = $(this);
                const colorId = color.val();
                const sizeId = size.val();

                // Kiểm tra biến thể đã tồn tại chưa
                const exists = container.find('input[name*="[color_id]"][value="' + colorId + '"]')
                    .filter(function() {
                        return $(this).closest('.card').find('input[name*="[size_id]"][value="' + sizeId + '"]').length > 0;
                    }).length > 0;

                if (!existingVariant) { // Chỉ tạo nếu biến thể chưa tồn tại trong DOM
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
                                        <label class="form-label small">Trạng thái Kho</label>
                                        <select name="variants[${currentIndex}][stock_status]" class="form-select form-select-sm stock-status" data-index="${currentIndex}">
                                            <option value="" selected>-- Chọn trạng thái kho --</option>
                                            <option value="0">Hết hàng</option>
                                            <option value="1">Còn hàng</option>
                                        </select>
                                        <input type="number" name="variants[${currentIndex}][stock_quantity]" class="form-control form-control-sm stock-quantity" style="display:none;" min="0" placeholder="Nhập số lượng">
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
                                        {{-- Không cần existing_image cho biến thể mới tạo ở đây vì nó chưa có --}}
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

        // Gắn lại sự kiện
        attachVariantEventListeners();
    });

    // Xử lý giá khuyến mãi
    $(document).on('input', 'input[name*="[sale_price]"]', function() {
        const index = $(this).attr('id').split('_')[2];
        const salePrice = parseFloat($(this).val()) || 0;
        const $startDate = $('#sale_start_date_' + index);
        const $endDate = $('#sale_end_date_' + index);

        if (salePrice > 0) {
            $startDate.prop('disabled', false);
            $endDate.prop('disabled', false);
        } else {
            $startDate.prop('disabled', true).val('');
            $endDate.prop('disabled', true).val('');
        }
    });

    // Gắn sự kiện ban đầu cho các input sale_price
    $('input[name*="[sale_price]"]').trigger('input');

    // Xử lý ảnh đại diện
    $('#thumbnail').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#img-main-preview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });
});

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

function attachVariantEventListeners() {
    // Xử lý nút ẩn/hiện chi tiết
    $('.btn-edit').off('click').on('click', function() {
        const $target = $($(this).data('bs-target'));
        $target.toggle();
        $(this).text($target.is(':visible') ? 'Ẩn' : 'Sửa');
    });

    // Xử lý nút xóa
    $('.btn-delete').off('click').on('click', function() {
        const $target = $($(this).data('bs-target'));
        const variantId = $target.find('input[name*="[id]"]').val();
        
        if (variantId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'variants_to_delete[]',
                value: variantId
            }).appendTo('form');
        }
        
        $target.remove();
    });
}
</script>
@endsection