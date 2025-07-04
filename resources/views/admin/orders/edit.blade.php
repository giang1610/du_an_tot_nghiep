@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <nav aria-label="breadcrumb" class="mt-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin" style="text-decoration: none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" style="text-decoration: none">Danh sách đơn hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cập nhật trạng thái đơn hàng</li>
        </ol>
    </nav>
    <form action="{{ route('orders.update', $order->id) }}" method="POST" class="bg-white rounded-3 shadow p-4">
        @csrf
        @method('PUT')
    <div class="d-flex align-items-center mb-4">
        <a href="/admin/orders" class="d-flex align-items-center text-decoration-none back-link">
            <i class="bi bi-arrow-left me-2" style="font-size: 1.5rem; color: #4b5563;"></i>
            <span class="text-dark">Quay lại danh sách đơn hàng</span>
        </a>
    </div>
    <h1>Sửa trang thái</h1>
    <div>
        <h3>Mã đơn hàng</h3>
        <input type="text" class="form-control mb-3" value="{{ $order->order_number }}" readonly>
    </div>
    <div>
        <h3>Khách hàng</h3>
        <input type="text" class="form-control mb-3" value="{{ $order->user->name }}" readonly>
    </div>
    <div>
        <h3>Email</h3>
        <input type="text" class="form-control mb-3" value="{{ $order->customer_email }}" readonly>
    </div>
    <div>
        <h3>Số điện thoại</h3>
        <input type="text" class="form-control mb-3" value="{{ $order->customer_phone }}" readonly>
    </div>
    <div>
        <h3>Ngày tạo </h3>
        <input type="date-timelocal" class="form-control mb-3" value="{{ $order->created_at->format('d/m/Y H:i') }}" readonly>
    </div>
    <div>
        <h3>Tổn tiền</h3>
        <input type="text" class="form-control mb-3" value="{{ $order->total }}" readonly>
    </div>
    @php
    // Tất cả trạng thái
        $statusOptions = [
            'cancelled' => 'Đã hủy',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'picking' => 'Đang lấy hàng',
            'shipping' => 'Đang giao hàng',
            'shipped' => 'Đã giao hàng',
            'completed' => 'Xác minh nhận hàng',
        ];

        // Flow hợp lệ (không gồm completed vì khách xác nhận)
        $statusFlow = ['pending', 'processing', 'picking', 'shipping', 'shipped'];

        $currentStatus = old('status', $order->status ?? 'pending');
        $currentIndex = array_search($currentStatus, $statusFlow);
        $nextStatus = $statusFlow[$currentIndex + 1] ?? null; // trạng thái kế tiếp
    @endphp

    <div class="mb-3">
        <label class="form-label">Trạng thái</label>
        <select name="status" class="form-select" required>
            {{-- Cho phép huỷ nếu chưa giao hàng --}}
            @if (!in_array($currentStatus, ['shipped', 'completed', 'cancelled']))
                <option value="cancelled" {{ $currentStatus == 'cancelled' ? 'selected' : '' }}>
                    {{ $statusOptions['cancelled'] }}
                </option>
            @endif

                        {{-- Trạng thái hiện tại (disabled) --}}
                        <option value="{{ $currentStatus }}" selected disabled>
                            {{ $statusOptions[$currentStatus] }} (hiện tại)
                        </option>

                        {{-- Trạng thái kế tiếp nếu có --}}
                        @if ($nextStatus)
                            <option value="{{ $nextStatus }}">
                                {{ $statusOptions[$nextStatus] }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Chỉ có thể chuyển sang trạng thái kế tiếp trong quy trình hoặc hủy đơn hàng.
                </div>
            </div>
        </div>

        <!-- Nút submit -->
        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="bi bi-check-circle me-2"></i>Cập nhật trạng thái
            </button>
        </div>
    </form>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .form-control:read-only, .form-select:disabled {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
</style>
@endsection