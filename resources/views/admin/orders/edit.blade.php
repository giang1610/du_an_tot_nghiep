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

    {{-- Form xử lý yêu cầu hoàn đơn --}}
    @if($order->status === 'return_requested')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Xử lý yêu cầu hoàn đơn</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Lý do khách hàng:</label>
                    <div class="border rounded p-2 bg-light">
                        {{ $order->return_reason ?? '— Không có lý do —' }}
                    </div>
                </div>
                <form action="{{ route('orders.handleReturn', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="action" class="form-label">Hành động:</label>
                        <select name="action" id="action" class="form-select">
                            <option value="accept">Đồng ý hoàn hàng</option>
                            <option value="reject">Từ chối hoàn hàng</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note_admin" class="form-label">Ghi chú admin:</label>
                        <textarea name="note_admin" id="note_admin" rows="3" class="form-control">{{ $order->note_admin }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Xử lý</button>
                </form>
            </div>
        </div>
    @endif

    <form action="{{ route('orders.update', $order->id) }}" method="POST" class="bg-white rounded-3 shadow p-4">
        @csrf
        @method('PUT')

        <!-- Header với nút quay lại -->
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <a href="/admin/orders" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Quay lại danh sách
                </a>
            </div>
            <h2 class="mb-0 text-primary">Cập nhật trạng thái đơn hàng</h2>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Thông tin đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Mã đơn hàng</label>
                            <input type="text" class="form-control bg-light" value="{{ $order->order_number }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Ngày tạo</label>
                            <input type="text" class="form-control bg-light" value="{{ $order->created_at->format('d/m/Y H:i') }}" readonly>
                        </div>
                        <div>
                            <label class="form-label text-muted small mb-1">Tổng tiền</label>
                            <input type="text" class="form-control bg-light fw-bold" value="{{ number_format($order->total) }} VNĐ" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Thông tin khách hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Khách hàng</label>
                            <input type="text" class="form-control bg-light" value="{{ $order->user->name ?? 'Khách vãng lai' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Email</label>
                            <input type="text" class="form-control bg-light" value="{{ $order->customer_email }}" readonly>
                        </div>
                        <div>
                            <label class="form-label text-muted small mb-1">Số điện thoại</label>
                            <input type="text" class="form-control bg-light" value="{{ $order->customer_phone }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trạng thái đơn hàng -->
        @php
            $statusOptions = [
                'cancelled' => 'Đã hủy',
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'picking' => 'Đang lấy hàng',
                'shipping' => 'Đang giao hàng',
                'shipped' => 'Đã giao hàng',
                'delivered' => 'Đã nhận hàng',
                'return_requested' => 'Yêu cầu trả hàng',
                'returning' => 'Đang trả hàng',
                'returned' => 'Đã trả hàng',
                'completed' => 'Đơn hàng hoàn thành',
                'failed_1' => 'Giao hàng thất bại lần 1',
                'failed_2' => 'Giao hàng thất bại lần 2',
                'failed' => 'Giao hàng thất bại',
            ];
            $statusFlow = ['pending', 'processing', 'picking', 'shipping', 'shipped'];
            $currentStatus = old('status', $order->status ?? 'pending');
            $currentIndex = array_search($currentStatus, $statusFlow);
            $nextStatus = $statusFlow[$currentIndex + 1] ?? null;
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Cập nhật trạng thái</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Trạng thái hiện tại</label>
                    <input type="text" class="form-control bg-light fw-bold"
                           value="{{ $statusOptions[$currentStatus] }}" readonly>
                </div>

                <div class="mb-3">
                    {{-- trạng thái này thì k đc edit --}}
                    @if (in_array($currentStatus, ['shipped', 'delivered', 'return_requested', 'returned', 'cancelled', 'failed']))
                        <input type="text" class="form-control bg-light fw-bold"
                            value="{{ $statusOptions[$currentStatus] }} (hiện tại)" readonly>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Đơn hàng đang ở trạng thái <b>{{ $statusOptions[$currentStatus] }}</b>. Không thể cập nhật trạng thái tiếp theo tại đây.
                        </div>
                    @else
                        <label class="form-label">Chọn trạng thái mới</label>
                        <select name="status" class="form-select" required>
                            @if (!in_array($currentStatus, ['shipped', 'completed', 'cancelled']))
                                <option value="cancelled" {{ $currentStatus == 'cancelled' ? 'selected' : '' }}>
                                    {{ $statusOptions['cancelled'] }}
                                </option>
                            @endif
                            <option value="{{ $currentStatus }}" selected disabled>
                                {{ $statusOptions[$currentStatus] }} (hiện tại)
                            </option>

                            {{-- Trạng thái kế tiếp nếu có --}}
                            {{-- @if ($nextStatus)
                                <option value="{{ $nextStatus }}">
                                    {{ $statusOptions[$nextStatus] }}
                                </option>
                            @endif --}}
                            @if ($currentStatus === 'shipping')
                                <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                <option value="failed_1">Giao hàng thất bại lần 1</option>
                            @elseif ($currentStatus === 'failed_1')
                                <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                <option value="failed_2">Giao hàng thất bại lần 2</option>
                            @elseif ($currentStatus === 'failed_2')
                                <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                <option value="failed">{{ $statusOptions['failed'] }}</option>
                            @elseif ($nextStatus)
                                <option value="{{ $nextStatus }}">{{ $statusOptions[$nextStatus] }}</option>
                            @endif
                        </select>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Chỉ có thể chuyển sang trạng thái kế tiếp trong quy trình hoặc hủy đơn hàng.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (!in_array($currentStatus, ['shipped', 'delivered', 'return_requested', 'returned']))
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-check-circle me-2"></i>Cập nhật trạng thái
                </button>
            </div>
        @endif
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
