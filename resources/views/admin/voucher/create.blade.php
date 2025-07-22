{{-- filepath: resources/views/admin/voucher/create.blade.php --}}
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i> Thêm Voucher</h2>
        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>
    
         
    
    <form action="{{ route('vouchers.store') }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tên voucher</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" >
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Mã voucher</label>
            <input type="text" name="code" class="form-control" value="{{ old('code') }}" >
            @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Loại voucher</label>
            <select name="type" class="form-select" >
                <option value="">-- Chọn loại --</option>
                <option value="shipping" {{ old('type') == 'shipping' ? 'selected' : '' }}>Giảm phí ship</option>
                <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>Giảm sản phẩm</option>
            </select>
            @error('type') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        
        <div class="mb-3">
        <label class="form-label">Loại giảm giá</label>
        <select name="discount_type" id="discount_type" class="form-select"  onchange="toggleDiscountInput()">
            <option value="">-- Chọn loại --</option>
            <option value="amount" {{ old('discount_type') == 'amount' ? 'selected' : '' }}>Số tiền giảm (VNĐ)</option>
            <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>Phần trăm giảm (%)</option>
        </select>
        @error('discount_type') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3" id="discount_amount_box" style="display: none;">
            <label class="form-label">Số tiền giảm (VNĐ)</label>
            <input type="number" name="discount_amount" class="form-control" value="{{ old('discount_amount') }}">
            @error('discount_amount') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3" id="discount_percent_box" style="display: none;">
            <label class="form-label">Phần trăm giảm (%)</label>
            <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent') }}">
            @error('discount_percent') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <script>
        function toggleDiscountInput() {
            var type = document.getElementById('discount_type').value;
            document.getElementById('discount_amount_box').style.display = (type === 'amount') ? 'block' : 'none';
            document.getElementById('discount_percent_box').style.display = (type === 'percent') ? 'block' : 'none';
        }
        toggleDiscountInput(); // Gọi khi load lại form (giữ old value)
        </script>
        <div class="mb-3">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}">
            @error('start_date') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Ngày kết thúc</label>
            <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}">
            @error('end_date') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Số lượng tổng</label>
            <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" >
            @error('quantity') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Số lần tối đa 1 user được dùng</label>
            <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}" >
            @error('usage_limit') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Thêm voucher</button>
       
            
    </form>
</div>
@endsection