@extends('admin.layouts.app')

@section('content')
<a href="/products">
    <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i> Quay lại danh sách sản phẩm
</a>

<div class="container">
    <h1 class="h4 mb-4">Thêm sản phẩm</h1>

    @if (session('error'))
    <div class="mb-0 alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label h4">Tên sản phẩm</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên danh mục" value="{{ old('name') }}">
            @error('name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label h4">Mô tả sản phẩm</label>
            <input type="text" name="description" id="description" class="form-control" placeholder="Nhập tên danh mục" value="{{ old('description') }}">
            @error('description')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
         <div class="mb-3">
            <label for="short_description" class="form-label h4">Mô tả ngắn</label>
            <input type="text" name="short_description" id="short_description" class="form-control" placeholder="Nhập tên danh mục" value="{{ old('short_description') }}">
            @error('short_description')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="slug" class="form-label h4">Đường dẫn danh mục</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn danh mục tự động tạo" value="{{ old('slug') }}">
            @error('slug')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
                    <label for="form-label">ảnh sản phẩm</label> <br>
                    <img id="img" style="max-width: 100px;"> <br>
                    <input type="file" name="thumbnail" class="form-control" onchange="img.src = window.URL.createObjectURL(this.files[0])">
                    @error('thumbnail')
                    <div class="text-danger">{{$message}}</div>
                    @enderror
        </div>
        <div class="mb-3">
                    <label class="form-label">Danh mục</label>
                    <select name="category_id" class="form-select">
                        
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div> 

                  {{-- Biến thể (có thể làm dạng group) --}}
    <div id="variants">
        <div class="variant">
            
        </div>
           </div>

    <div class="mb-3">
    <label class="form-label">Màu sắc</label>
    <select name="color_id[]" class="form-select select2-color" multiple id="colorSelect">
        @foreach($colors as $color)
            <option value="{{ $color->id }}"
                data-name="{{ $color->name }}"
                {{ in_array($color->id, old('color_id', [])) ? 'selected' : '' }}>
                {{ $color->name }}
            </option>
        @endforeach
    </select>
    <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-color">Chọn tất cả màu</button>
    </div>


    <div class="mb-3">
    <label class="form-label">Kích cỡ</label>
    <select name="size_id[]" class="form-select select2-size" multiple id="sizeSelect">
        @foreach($sizes as $size)
            <option value="{{ $size->id }}"
                data-name="{{ $size->name }}"
                {{ in_array($size->id, old('size_id', [])) ? 'selected' : '' }}>
                {{ $size->name }}
            </option>
        @endforeach
    </select>
    <button type="button" class="btn btn-sm btn-primary mt-2 select-all" data-target=".select2-size">Chọn tất cả size</button>
</div>


    <button type="button" class="btn btn-primary mb-3" id="generateVariants">Tạo các biến thể</button>
    <input type="hidden" name="has_variants" value="1">

    <!-- Vùng hiển thị tổ hợp biến thể -->
    <div id="variantContainer"></div>
   <script>
    document.getElementById('generateVariants').addEventListener('click', function () {
        const colors = Array.from(document.querySelectorAll('#colorSelect option:checked'));
        const sizes = Array.from(document.querySelectorAll('#sizeSelect option:checked'));
        const container = document.getElementById('variantContainer');
        container.innerHTML = ''; // Xóa trước khi tạo mới

        let index = 0;
        colors.forEach(color => {
            sizes.forEach(size => {
                const variantId = `variant-${index}`;
                const html = `
                    <div class="card p-3 mb-3 border" id="${variantId}">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>${color.dataset.name} - ${size.dataset.name}</strong>
                            <div>
                                <button type="button" class="btn btn-sm btn-warning btn-edit" data-target="#details-${variantId}">Sửa</button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-target="#${variantId}">Xóa</button>
                            </div>
                        </div>

                        <input type="hidden" name="variants[${index}][color_id]" value="${color.value}">
                        <input type="hidden" name="variants[${index}][size_id]" value="${size.value}">

                        <div class="variant-details mt-3" id="details-${variantId}" style="display: none;">
                            <div class="mb-2">
                                <label>Mã SKU</label>
                                <input type="text" name="variants[${index}][sku]" class="form-control">
                            </div>

                            <div class="mb-2">
                                <label>Giá</label>
                                <input type="number" name="variants[${index}][price]" class="form-control">
                            </div>

                            <div class="mb-2">
                                <label>Giá khuyến mãi</label>
                                <input type="number" name="variants[${index}][sale_price]" class="form-control" id="sale_price_${index}">
                            </div>
                            <div class="mb-2">
                                <label>Ngày bắt đầu khuyến mãi</label>
                                <input type="datetime-local" name="variants[${index}][sale_start_date]" class="form-control" id="sale_start_date_${index}">
                            </div>
                            <div class="mb-2">
                                <label>Ngày kết thúc khuyến mãi</label>
                                <input type="datetime-local" name="variants[${index}][sale_end_date]" class="form-control" id="sale_end_date_${index}">
                            </div>
                            

                           <div class="mb-2">
                            <label>Kho</label>
                            <select name="variants[${index}][stock]" class="form-select">
                                <option value="">-- Chọn kho --</option>
                                <option value="0">còn hàng</option>
                                <option value="1">hết hàng</option>
                            </select>
                        </div>


                           <div class="mb-2"> 
                                <label>Ảnh</label> <br>
                                <img style="max-width: 100px;" class="preview-image mb-2"> <br>
                                <input type="file" name="variants[${index}][image]" class="form-control" onchange="previewImage(this)">
                            </div>

                           

                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
                index++;
            });
        });

        // Sự kiện nút "Sửa" để hiển thị/ẩn phần nhập chi tiết
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const detailSection = document.querySelector(this.dataset.target);
                if (detailSection.style.display === 'none') {
                    detailSection.style.display = 'block';
                    this.textContent = 'Ẩn';
                } else {
                    detailSection.style.display = 'none';
                    this.textContent = 'Sửa';
                }
            });
        });

        // Sự kiện nút "Xóa" để xóa tổ hợp biến thể
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const variantCard = document.querySelector(this.dataset.target);
                variantCard.remove();
            });
        });
    });
    </script>
    <script>
        function previewImage(input) {
            const img = input.closest('.mb-2').querySelector('.preview-image');
            if (input.files && input.files[0]) {
                img.src = URL.createObjectURL(input.files[0]);
            }
        }
        </script>




        <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select" required>
                <option value="0" selected>Chưa xuất bản</option>
                <option value="1" >Hoạt động</option>
                <option value="2">Tạm dừng</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Thêm</button>
        </div>
    </form>
</div>
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

<script>
            const salePriceInput = document.getElementById('sale_price');
            const startDateInput = document.getElementById('sale_start_date');
            const endDateInput = document.getElementById('sale_end_date');

            salePriceInput.addEventListener('input', function () {
                if (this.value.trim() !== '') {
                    startDateInput.disabled = false;
                    endDateInput.disabled = false;
                } else {
                    startDateInput.disabled = true;
                    endDateInput.disabled = true;
                    startDateInput.value = '';
                    endDateInput.value = '';
                }
            });
        </script>
<!-- Link thư viện Select2 nếu chưa có -->
<!-- Select2 CSS -->


  <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JS Select2 -->
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



