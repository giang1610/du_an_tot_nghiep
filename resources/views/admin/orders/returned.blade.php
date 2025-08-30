@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <nav aria-label="breadcrumb" class="mt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin" style="text-decoration: none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" style="text-decoration: none">Danh sách đơn hàng</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.returned') }}" style="text-decoration: none">Đã trả hàng</a></li>
        </ol>
    </nav>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Bộ lọc đơn hàng
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Mã đơn, tên KH..."
                            value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label for="from_date" class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                        value="{{ request('from_date') }}">
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label for="to_date" class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                        value="{{ request('to_date') }}">
                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1 d-none d-md-inline"></i>
                        <span class="d-inline">Lọc</span>
                    </button>
                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync-alt me-1 d-none d-md-inline"></i>
                        <span class="d-inline">Xóa</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-table me-1"></i>
                Danh sách đơn hàng
                <span class="badge bg-primary ms-2">{{ $orders->total() }} đơn</span>
            </div>
            @if(request()->hasAny(['search', 'status', 'payment_method', 'payment_status', 'from_date', 'to_date']))
            <div class="text-muted small">
                <div class="d-flex flex-wrap gap-1">
                    Đang lọc:
                    @if(request('search')) <span class="badge bg-info">Tìm: {{ request('search') }}</span> @endif
                    @if(request('status')) <span class="badge bg-info">Trạng thái: {{ request('status') }}</span> @endif
                    @if(request('payment_method')) <span class="badge bg-info">PTTT: {{ request('payment_method') }}</span> @endif
                    @if(request('payment_status')) <span class="badge bg-info">TTTT: {{ request('payment_status') }}</span> @endif
                    @if(request('from_date')) <span class="badge bg-info">Từ: {{ request('from_date') }}</span> @endif
                    @if(request('to_date')) <span class="badge bg-info">Đến: {{ request('to_date') }}</span> @endif
                </div>
            </div>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover d-none d-md-table">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th>Địa chỉ</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức & Trạng thái</th>
                            <th>TT giao hàng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number ?? 'ORD-' . $order->id }}</strong>
                                <div class="text-muted small">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                {{ $order->user->name ?? 'Khách vãng lai' }}
                                <div class="text-muted small">
                                    {{ $order->customer_phone }}
                                </div>
                            </td>
                            <td>
                                @foreach($order->items as $item)
                                <div class="d-flex align-items-center mb-2">
                                    @if($item->variant->product->image)
                                    <img src="{{ asset($item->variant->product->image) }}"
                                        class="img-thumbnail me-2"
                                        width="40"
                                        alt="{{ $item->variant->product->name }}">
                                    @endif
                                    <div>
                                        {{ $item->variant->product->name ?? 'N/A' }}
                                        @if($item->variant->color || $item->variant->size)
                                        <div class="text-muted small">
                                            {{ $item->variant->color->name ?? '' }} |
                                            {{ $item->variant->size->name ?? '' }}
                                            x{{ $item->quantity }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="fas fa-truck me-2"></i> {{ $order->shipping_method }}</div>
                                    <div><i class="fas fa-map-marker-alt me-2"></i> {{ Str::limit($order->shipping_address, 10) }}</div>
                                </div>
                            </td>
                              <td>
                                <strong>{{ number_format($order->total) }} VNĐ</strong>
                                @if($order->discount > 0)
                                <div class="text-danger small">
                                    <i class="fas fa-tag me-1"></i> Giảm {{ number_format($order->discount) }}₫
                                </div>
                                @endif
                            </td>
                            <td>
                                @switch($order->payment_method)
                                @case('cod')
                                <span class="badge bg-info">
                                    <i class="fas fa-money-bill-wave me-1"></i> COD
                                </span>
                                @break
                                @case('momo')
                                <span style="background-color: #A50064; color: white" class="badge ">
                                    <i class="fas fa-mobile-alt me-1"></i> Momo
                                </span>
                                @break
                                 @case('vnpay')
                                    <span class="badge bg-success">
                                        <i class="fas fa-credit-card me-1"></i> vnpay
                                    </span>
                                    @break
                                @default
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-question me-1"></i> Khác
                                </span>
                                @endswitch
                                <div class="small mt-1">
                                    @if($order->payment_status == 'paid')
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i> Đã thanh toán
                                    </span>
                                    @else
                                    <span class="text-warning">
                                        <i class="fas fa-clock me-1"></i> Chưa thanh toán
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @switch($order->status)
                                @case('pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i> Chờ xử lý
                                </span>
                                @break
                                @case('processing')
                                <span class="badge bg-primary">
                                    <i class="fas fa-cog me-1"></i> Đang xử lý
                                </span>
                                @break
                                @case('picking')
                                <span class="badge bg-info">
                                    <i class="fas fa-box-open me-1"></i> Đang lấy hàng
                                </span>
                                @break
                                @case('shipping')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-truck me-1"></i> Đang giao hàng
                                </span>
                                @break
                                @case('shipped')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Đã giao hàng
                                </span>
                                @break
                                @case('delivered')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Đã nhận hàng
                                </span>
                                @break
                                @case('completed')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-double me-1"></i> Hoàn thành
                                </span>
                                @break
                                @case('failed')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Giao hàng thất bại
                                </span>
                                @break
                                 @case('failed_1')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Giao hàng thất bại lần 1
                                </span>
                                @break
                                 @case('failed_2')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Giao hàng thất bại lần 2
                                </span>
                                @break
                                @case('returning')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-undo me-1"></i> Đang hoàn hàng
                                </span>
                                @break
                                @case('return_requested')
                                <span class="badge bg-info">
                                    <i class="fas fa-exchange-alt me-1"></i> Yêu cầu hoàn hàng
                                </span>
                                @break
                                @case('returned')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-undo-alt me-1"></i> Hoàn hàng
                                </span>
                                @break
                                @case('cancelled')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Đã hủy
                                </span>
                                @break
                                @default
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-question me-1"></i> Không rõ
                                </span>
                                @endswitch
                            </td>
                            <td>
                                 <div class="d-flex gap-2 align-items-center">
                                        <a href="{{ route('orders.show', $order->id) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="tooltip"
                                            title="Xem chi tiết">
                                            
                                            <i class="fas fa-eye"></i>
                                        </a>

                                   
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Không có đơn hàng nào</h5>
                                    @if(request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-sync-alt me-1"></i> Xóa bộ lọc
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Mobile view -->
                <div class="d-md-none">
                    @forelse ($orders as $order)
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between">
                            <div>
                                <strong>{{ $order->order_number ?? 'ORD-' . $order->id }}</strong>
                                <div class="text-muted small">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div>
                                @switch($order->status)
                                @case('pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i> Chờ xử lý
                                </span>
                                @break
                                @case('processing')
                                <span class="badge bg-primary">
                                    <i class="fas fa-cog me-1"></i> Đang xử lý
                                </span>
                                @break
                                @case('picking')
                                <span class="badge bg-info">
                                    <i class="fas fa-box-open me-1"></i> Đang lấy hàng
                                </span>
                                @break
                                @case('shipping')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-truck me-1"></i> Đang giao hàng
                                </span>
                                @break
                                @case('shipped')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Đã giao hàng
                                </span>
                                @break
                                @case('completed')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-double me-1"></i> Hoàn thành
                                </span>
                                @break
                                @case('cancelled')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Đã hủy
                                </span>
                                @break
                                @default
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-question me-1"></i> Không rõ
                                </span>
                                @endswitch

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Khách hàng:</strong>
                                {{ $order->user->name ?? 'Khách vãng lai' }} ({{ $order->customer_phone }})
                            </div>

                            <div class="mb-2">
                                <strong>Sản phẩm:</strong>
                                @foreach($order->items as $item)
                                <div class="d-flex align-items-center mb-1">
                                    @if($item->variant->product->image)
                                    <img src="{{ asset($item->variant->product->image) }}"
                                        class="img-thumbnail me-2"
                                        width="30"
                                        alt="{{ $item->variant->product->name }}">
                                    @endif
                                    <div>
                                        {{ $item->variant->product->name ?? 'N/A' }}
                                        @if($item->variant->color || $item->variant->size)
                                        <div class="text-muted small">
                                            {{ $item->variant->color->name ?? '' }} |
                                            {{ $item->variant->size->name ?? '' }}
                                            x{{ $item->quantity }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mb-2">
                                <strong>Giao hàng:</strong>
                                <div class="small">
                                    <div><i class="fas fa-truck me-2"></i> {{ $order->shipping_method }}</div>
                                    <div><i class="fas fa-map-marker-alt me-2"></i> {{ $order->shipping_address }}</div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <strong>Thanh toán:</strong>
                                @switch($order->payment_method)
                                @case('cod')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-money-bill-wave me-1"></i> COD
                                </span>
                                @break
                                @case('momo')
                                <span class="badge bg-danger">
                                    <i class="fas fa-mobile-alt me-1"></i> Momo
                                </span>
                                @break
                                @default
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-question me-1"></i> Khác
                                </span>
                                @endswitch
                                @if($order->payment_status == 'paid')
                                <span class="text-success ms-2">
                                    <i class="fas fa-check-circle me-1"></i> Đã thanh toán
                                </span>
                                @else
                                <span class="text-warning ms-2">
                                    <i class="fas fa-clock me-1"></i> Chưa thanh toán
                                </span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>Tổng tiền:</strong>
                                <span class="fw-bold">{{ number_format($order->total) }}₫</span>
                                @if($order->discount > 0)
                                <span class="text-danger small ms-2">
                                    <i class="fas fa-tag me-1"></i> Giảm {{ number_format($order->discount) }}₫
                                </span>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('orders.show', $order->id) }}"
                                    class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye me-1"></i> Chi tiết
                                </a>
                               
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có đơn hàng nào</h5>
                            @if(request()->hasAny(['search', 'status', 'from_date', 'to_date']))
                            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-sync-alt me-1"></i> Xóa bộ lọc
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            @if($orders->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                <div class="text-muted small mb-2 mb-md-0">
                    Hiển thị {{ $orders->firstItem() }} đến {{ $orders->lastItem() }} trong tổng số {{ $orders->total() }} đơn hàng
                </div>
                {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .img-thumbnail {
        max-height: 40px;
        object-fit: cover;
    }

    .badge {
        font-weight: 500;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    @media (max-width: 767.98px) {
        .card-body {
            padding: 0.75rem;
        }

        .img-thumbnail {
            max-height: 30px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Enable tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush