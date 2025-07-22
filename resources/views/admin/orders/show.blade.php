@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <nav aria-label="breadcrumb" class="mt-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin" style="text-decoration: none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" style="text-decoration: none">Danh sách đơn hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết đơn hàng</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Quay lại danh sách </a>
        <div class="btn-group">
            @if(!in_array($order->status, ['completed', 'cancelled']))
            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square me-2"></i>Cập nhật trạng thái
            </a> @endif
            <button class="btn btn-success" onclick="printOrder()">
                <i class="bi bi-printer me-2"></i>In đơn hàng
            </button>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"> Đơn hàng #{{ $order->order_number }}
                <span class="badge
                @switch($order->status)
                @case('pending') bg-warning text-dark @break
                @case('processing') bg-primary @break
                @case('picking') bg-info text-dark @break
                @case('shipping') bg-secondary @break
                @case('shipped') bg-success @break
                @case('completed') bg-success @break
                @case('cancelled') bg-danger @break
                @default bg-light text-dark @endswitch ms-2"> {{ match($order->status)
                    {'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'picking' => 'Đang lấy hàng',
                    'shipping' => 'Đang giao hàng',
                    'shipped' => 'Đã giao hàng',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                    default => ucfirst($order->status) } }} </span>
            </h3>
            <div class="mb-2 mb-md-0">
                <i class="bi bi-calendar-event me-2"></i>
                Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-person "></i>Thông tin khách hàng</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong><i class="bi bi-envelope me-2"></i>Email:</strong>
                                    {{ $order->customer_email ?? 'Không có' }}
                                </li>
                                <li class="mb-2">
                                    <strong><i class="bi bi-telephone me-2"></i>Điện thoại:</strong>
                                    {{ $order->customer_phone }}
                                </li>
                                <li class="mb-0">
                                    <strong><i class="bi bi-geo-alt me-2"></i>Địa chỉ:</strong>
                                    {{ $order->shipping_address }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-truck"></i>Thanh toán & Giao hàng</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Phương thức thanh toán:</strong>
                                    <span class="badge
                                    @switch($order->payment_method)
                                        @case('cod') bg-success @break
                                        @case('momo') momo-payment @break
                                        @default bg-light text-dark
                                    @endswitch">
                                        {{
                                    match($order->payment_method) {
                                        'cod' => 'COD',
                                        'momo' => 'Momo',
                                        default => $order->payment_method
                                    }
                                }}
                                    </span>
                                </li>
                                <!-- <li class="mb-2">
                                    <strong>Phương thức vận chuyển:</strong>
                                    {{ $order->shipping_method ?? 'Tiêu chuẩn' }}
                                </li> -->
                                <li class="mb-2">
                                    <strong>Trạng thái thanh toán:</strong>
                                    @if($order->payment_status == 'paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Đã thanh toán
                                    </span>
                                    @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Chưa thanh toán
                                    </span>
                                    @endif
                                </li>
                                <li class="mb-0">
                                    <strong>Ghi chú:</strong>
                                    {{ $order->notes ?: 'Không có ghi chú' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-cart me-2"></i>Sản phẩm đã đặt
            </h5>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Sản phẩm</th>
                            <th class="d-none d-md-table-cell">Phân loại</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $index => $item)
                        @php
                        $variant = $item->variant;
                        $product = $variant->product ?? null;
                        $thumbnail = $variant->image ?? ($product?->image ?? null);
                        $price = $item->price;
                        $salePrice = $item->sale_price ?? $price;
                        $totalPrice = $salePrice * $item->quantity;
                        $hasDiscount = $salePrice < $price;
                            @endphp
                            <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($thumbnail)
                                    <img src="{{ asset('storage/' . $thumbnail) }}"
                                        class="img-thumbnail me-2 me-md-3"
                                        width="50"
                                        alt="{{ $product->name ?? '' }}">
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $product->name ?? 'Không rõ' }}</div>
                                        <small class="text-muted d-md-none">
                                            @if($variant->color)
                                            Màu: {{ $variant->color->name }}
                                            @endif
                                            @if($variant->size)
                                            | Size: {{ $variant->size->name }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($variant->color)
                                <div class="d-flex align-items-center mb-1">
                                    <span class="me-2">Màu:</span>
                                    @if($variant->color->image)
                                    <!-- <img src="{{ asset('storage/' . $variant->color->image) }}"
                                        width="18"
                                        class="rounded-circle border me-1"
                                        alt="{{ $variant->color->name }}"> -->
                                    @endif
                                    <span>{{ $variant->color->name }}</span>
                                </div>
                                @endif

                                @if($variant->size)
                                <div class="mb-1">Size: {{ $variant->size->name }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">
                                @if($hasDiscount)
                                <div class="text-decoration-line-through text-muted small">
                                    {{ number_format($price) }} VNĐ
                                </div>
                                <div class="text-danger fw-bold">
                                    {{ number_format($salePrice) }} VNĐ
                                </div>
                                @else
                                <div class="fw-bold">
                                    {{ number_format($price) }} VNĐ
                                </div>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($totalPrice) }} VNĐ
                            </td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tổng kết đơn hàng -->
            <div class="row justify-content-end mt-4">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <h5 class="card-title border-bottom pb-2 mb-3">Tổng kết đơn hàng</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between mb-2">
                                    <span>Tạm tính:</span>
                                    <span>{{ number_format($order->subtotal) }} VNĐ</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span>Phí vận chuyển:</span>
                                    <span>{{ number_format($order->shipping) }} VNĐ</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span>Thuế (VAT):</span>
                                    <span>{{ number_format($order->tax) }} VNĐ</span>
                                </li>
                                @if($order->discount > 0)
                                <li class="d-flex justify-content-between mb-2 text-danger">
                                    <span>Giảm giá:</span>
                                    <span>-{{ number_format($order->discount) }} VNĐ</span>
                                </li>
                                @endif
                                <li class="d-flex justify-content-between mt-3 pt-2 border-top fw-bold fs-5">
                                    <span>Tổng cộng:</span>
                                    <span>{{ number_format($order->total) }} VNĐ</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .img-thumbnail {
        object-fit: cover;
        height: 50px;
        width: 50px;
    }

    .card-header {
        padding: 0.75rem 1.25rem;
    }

    @media (max-width: 767.98px) {
        .card-body {
            padding: 1rem;
        }

        .table td,
        .table th {
            padding: 0.5rem;
        }
    }


    @media print {
        /* Ẩn tất cả các phần không liên quan */
        body > *:not(.print-content) {
            display: none !important;
        }
        /* Hiển thị phần nội dung in */
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            background: white;
        }
        /* Tối ưu hiển thị khi in */
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .table {
            width: 100% !important;
            font-size: 14px !important;
        }
        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }
        /* Ẩn các nút và phần không cần in */
        .btn, .d-print-none {
            display: none !important;
        }
    }
    .momo-payment {
    background-color: #A50064 !important; /* Màu chính thức của Momo */
    color: white !important;
}
</style>

<script>
    function printOrder() {
        // Thêm lớp print-content vào phần cần in
        const content = document.querySelector('.container-fluid').cloneNode(true);
        content.classList.add('print-content');

        // Xóa các phần không cần in
        const elementsToRemove = content.querySelectorAll('.d-print-none, .btn');
        elementsToRemove.forEach(el => el.remove());

        // Mở cửa sổ in mới
        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial; margin: 0; padding: 20px; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { border: 1px solid #ddd; padding: 8px; }
                    .badge { border: 1px solid #000 !important; color: #000 !important; background: none !important; }
                    .text-danger { color: #dc3545 !important; }
                    .fw-bold { font-weight: bold !important; }
                    h2, h3, h4, h5 { color: #000 !important; margin-top: 10px; }
                    .card { border: none !important; box-shadow: none !important; }
                    .border-bottom { border-bottom: 1px solid #ddd !important; }
                </style>
            </head>
            <body>
                ${content.innerHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        // Tự động in sau khi tải xong
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
@endsection
