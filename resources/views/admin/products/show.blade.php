@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/admin/products" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <h1 class="h4 mb-4">Chi tiết sản phẩm</h1>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-medium">Tên sản phẩm</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên sản phẩm" value="{{ old('name', $product->name) }}" disabled>
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-medium">Đường dẫn</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn tự động tạo" value="{{ old('slug', $product->slug) }}" disabled>
                        @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label fw-medium">Mô tả sản phẩm</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả sản phẩm" rows="4" readonly>{{ old('description', $product->description) }}</textarea>
                    </div>
                    
                    <div class="col-6">
                        <label for="short_description" class="form-label fw-medium">Mô tả ngắn</label>
                        <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập mô tả ngắn" value="{{ old('short_description', $product->short_description) }}" disabled>
                        @error('short_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-6">
                        <label for="price_products" class="form-label fw-medium">Giá sản phẩm</label>
                        <input type="number" name="price_products" id="price_products" class="form-control" placeholder="Nhập giá sản phẩm" value="{{ old('price_products', (int)$product->price_products) }}" disabled>
                        @error('price_products')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="thumbnail" class="form-label">Ảnh sản phẩm chính</label>
                        <div class="mb-2">
                            <img id="img-main-preview" style="max-width: 100px; {{ $product->thumbnail ? 'display: block;' : 'display: none;' }}" 
                                 src="{{ $product->thumbnail ? asset('storage/'.$product->thumbnail) : '' }}" disabled>
                        </div>
                        
                       
                    </div>

                   <div class="col-md-6">
                        <label class="form-label fw-medium">Danh mục</label>
                        <select name="category_id" class="form-select" disabled>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                    <label class="form-label fw-medium">Trạng thái</label>
                    <input type="text" class="form-control" readonly
                            value="@switch($product->status)
                                    @case(0) Chưa xuất bản @break
                                    @case(1) Hoạt động @break
                                    @case(2) Tạm dừng @break
                                    @default Không xác định
                                    @endswitch">
                    </div>
                </div>
            </div>
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
                                       value="{{ old('variants.'.$i.'.sku', $variant['sku'] ?? '') }}" disabled>
                                
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Trạng thái Kho</label>
                                <div class="form-control bg-light d-flex align-items-center" style="min-height: 38px; cursor: not-allowed;">
                                    @if((string)old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '0') == '1')
                                        <span class="badge bg-success me-2">Còn hàng</span>
                                        <span class="text-muted small">{{ old('variants.'.$i.'.stock_quantity', $variant['stock_quantity'] ?? 0) }} sản phẩm</span>
                                    @else
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @endif
                                </div>
                                <input type="hidden" name="variants[{{ $i }}][stock_status]" value="{{ old('variants.'.$i.'.stock_status', $variant['stock_status'] ?? '0') }}">
                                <input type="hidden" name="variants[{{ $i }}][stock_quantity]" value="{{ old('variants.'.$i.'.stock_quantity', $variant['stock_quantity'] ?? 0) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Giá</label>
                                <input type="number" name="variants[{{ $i }}][price]" class="form-control form-control-sm" 
                                       value="{{ old('variants.'.$i.'.price', (int)$variant['price'] ?? '') }}" disabled>
                                
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Giá khuyến mãi</label>
                                <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control form-control-sm" 
                                       id="sale_price_{{ $i }}" value="{{ old('variants.'.$i.'.sale_price', isset($variant['sale_price']) && $variant['sale_price'] !== null ? (is_numeric($variant['sale_price']) ? rtrim(rtrim($variant['sale_price'], '0'), '.') : $variant['sale_price']) : '') }}" disabled>
                                @error('variants.'.$i.'.sale_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Ngày bắt đầu khuyến mãi</label>
                                @if(!empty(old('variants.'.$i.'.sale_start_date', $variant['sale_start_date'] ?? null)))
                                    <p class="form-control-static bg-light p-2 rounded">
                                        {{ \Carbon\Carbon::parse(old('variants.'.$i.'.sale_start_date', $variant['sale_start_date']))->format('d/m/Y H:i') }}
                                    </p>
                                    <input type="hidden" name="variants[{{ $i }}][sale_start_date]" 
                                        value="{{ old('variants.'.$i.'.sale_start_date', $variant['sale_start_date'] ?? '') }}">
                                @else
                                    <p class="form-control-static bg-light p-2 rounded">Không có</p>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small">Ngày kết thúc khuyến mãi</label>
                                @if(!empty(old('variants.'.$i.'.sale_end_date', $variant['sale_end_date'] ?? null)))
                                    <p class="form-control-static bg-light p-2 rounded">
                                        {{ \Carbon\Carbon::parse(old('variants.'.$i.'.sale_end_date', $variant['sale_end_date']))->format('d/m/Y H:i') }}
                                    </p>
                                    <input type="hidden" name="variants[{{ $i }}][sale_end_date]" 
                                        value="{{ old('variants.'.$i.'.sale_end_date', $variant['sale_end_date'] ?? '') }}">
                                    @else
                                    <p class="form-control-static bg-light p-2 rounded">Không có</p>
                              
                                @endif
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small">Ảnh biến thể</label>
                                <div class="mb-2">
                                    <img class="preview-image-variant mb-2 rounded"
                                         style="max-width: 100px; {{ isset($variant['image']) ? 'display: block;' : 'display: none;' }}"
                                         src="{{ isset($variant['image']) ? asset('storage/'.$variant['image']) : '' }}"
                                         alt="Preview" id="img-preview-{{ $i }}">
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


@endsection