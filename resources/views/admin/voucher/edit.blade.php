{{-- filepath: resources/views/admin/voucher/edit.blade.php --}}
@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="mb-0 fw-bold"></i>🎟️ Sửa Voucher</h2>
        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>
    <form action="{{ route('vouchers.update', $voucher->id) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tên voucher</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $voucher->name) }}">
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Mã voucher</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $voucher->code) }}">
                @error('code') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Loại voucher</label>
                <select name="type" class="form-select">
                    <option value="">-- Chọn loại --</option>
                    <option value="shipping" {{ old('type', $voucher->type) == 'shipping' ? 'selected' : '' }}>Giảm phí ship</option>
                    <option value="product" {{ old('type', $voucher->type) == 'product' ? 'selected' : '' }}>Giảm sản phẩm</option>
                </select>
                @error('type') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row mb-3">
            
            <div class="col-md-6">
                <label class="form-label">Loại giảm giá</label>
                <select name="discount_type" id="discount_type" class="form-select" onchange="toggleDiscountInput()">
                    <option value="">-- Chọn loại --</option>
                    <option value="amount" {{ old('discount_type', $voucher->discount_type) == 'amount' ? 'selected' : '' }}>Số tiền giảm  (VNĐ)</option>
                    <option value="percent" {{ old('discount_type', $voucher->discount_type) == 'percent' ? 'selected' : '' }}>Phần trăm giảm (%)</option>
                </select>
                @error('discount_type') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6" id="discount_amount_box" style="display: none;">
                <label class="form-label">Số tiền giảm (VNĐ)</label>
                <input type="number" name="discount_amount" class="form-control"
                    value="{{ $voucher->discount_type === 'amount' ? old('discount_amount', (int) $voucher->discount_amount ) : '' }}">
                @error('discount_amount') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6" id="discount_percent_box" style="display: none;">
                <label class="form-label">Phần trăm giảm (%)</label>
                <input type="number" name="discount_percent" class="form-control"
                    value="{{ $voucher->discount_type === 'percent' ? (int)old('discount_percent', $voucher->discount_percent) : '' }}">
                @error('discount_percent') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Ngày bắt đầu</label>
                <input type="datetime-local" name="start_date" class="form-control"
                    value="{{ old('start_date', \Carbon\Carbon::parse($voucher->start_date)->format('Y-m-d\TH:i')) }}">
                @error('start_date') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Ngày kết thúc</label>
                <input type="datetime-local" name="end_date" class="form-control"
                    value="{{ old('end_date', \Carbon\Carbon::parse($voucher->end_date)->format('Y-m-d\TH:i')) }}">
                @error('end_date') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Số lượng tổng</label>
                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $voucher->quantity) }}">
                @error('quantity') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Số lần tối đa 1 user được dùng</label>
                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $voucher->usage_limit) }}">
                @error('usage_limit') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Sửa voucher</button>
    </form>
</div>
@endsection

<script>
    function toggleDiscountInput() {
        var type = document.getElementById('discount_type').value;
        var amountBox = document.getElementById('discount_amount_box');
        var percentBox = document.getElementById('discount_percent_box');
        
        if (type === 'amount') {
            amountBox.style.display = 'block';
            percentBox.style.display = 'none';
            percentBox.querySelector('input').value = '';
        } else if (type === 'percent') {
            amountBox.style.display = 'none';
            percentBox.style.display = 'block';
            amountBox.querySelector('input').value = '';
        } else {
            amountBox.style.display = 'none';
            percentBox.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleDiscountInput();
        document.getElementById('discount_type').addEventListener('change', toggleDiscountInput);
    });
</script>