@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Thông tin thanh toán</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('checkout.process') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="shipping_address">Địa chỉ giao hàng</label>
                            <input type="text" class="form-control" id="shipping_address" 
                                name="shipping_address" required 
                                value="{{ old('shipping_address', $user->address ?? '') }}">
                            @error('shipping_address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_address">Địa chỉ thanh toán (nếu khác địa chỉ giao hàng)</label>
                            <input type="text" class="form-control" id="billing_address" 
                                name="billing_address" 
                                value="{{ old('billing_address', $user->billing_address ?? '') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone">Số điện thoại</label>
                            <input type="text" class="form-control" id="customer_phone" 
                                name="customer_phone" required 
                                value="{{ old('customer_phone', $user->phone ?? '') }}">
                            @error('customer_phone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method">Phương thức thanh toán</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>
                                    Thanh toán khi nhận hàng (COD)
                                </option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>
                                    Thẻ tín dụng
                                </option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                    Chuyển khoản ngân hàng
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Ghi chú đơn hàng (tùy chọn)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Hoàn tất đặt hàng
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Đơn hàng của bạn</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($items as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="my-0">{{ $item->product_name }}</h6>
                                <small class="text-muted">
                                    {{ $item->quantity }} x {{ number_format($item->price) }}₫
                                </small>
                            </div>
                            <span class="text-muted">{{ number_format($item->subtotal) }}₫</span>
                        </li>
                        @endforeach
                        
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tạm tính</span>
                            <strong>{{ number_format($total) }}₫</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Phí vận chuyển</span>
                            <strong>10.000₫</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Thuế (10%)</span>
                            <strong>{{ number_format($total * 0.1) }}₫</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Tổng cộng</strong></span>
                            <strong>{{ number_format($total + 10000 + ($total * 0.1)) }}₫</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection