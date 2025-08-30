@extends('admin.layouts.app')
@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">🎟️ Quản lý Voucher</h4>
        <a href="{{ route('vouchers.create') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Thêm voucher
        </a>
    </div>

    <form action="{{ route('vouchers.index') }}" method="GET" class="mb-4">
        <div class="input-group shadow-sm rounded">
            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên voucher..." value="{{ request()->get('search') }}">
            <button class="btn btn-primary" type="submit">
                Tìm kiếm
            </button>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Mã</th>
                            <th class="text-start">Tên voucher</th>
                            <th>Loại</th>
                            <th>Số lượng</th>
                            <th>Giảm</th>
                            <th>Giới hạn/user</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->id }}</td>
                                <td><code>{{ $voucher->code }}</code></td>
                                <td class="text-start fw-semibold">{{ $voucher->name }}</td>
                                <td>
                                    @if($voucher->type == 'shipping')
                                        <span class="badge bg-info-subtle text-info" title="Voucher giảm phí vận chuyển">
                                            <i class="bi bi-truck"></i> Ship
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary" title="Voucher giảm giá sản phẩm">
                                            <i class="bi bi-tag"></i> Sản phẩm
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->quantity >= 1)
                                        <span class="badge bg-success-subtle text-success">{{ $voucher->quantity }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Hết</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->discount_type == 'percent')
                                        <span class="text-success" title="Giảm theo phần trăm">{{ (int)$voucher->discount_percent }}%</span>
                                    @elseif($voucher->discount_type == 'amount')
                                        <span class="text-warning" title="Giảm theo số tiền">{{ number_format($voucher->discount_amount, 0, ',', '.') }}đ</span>
                                    @else
                                        <em class="text-muted">Không rõ</em>
                                    @endif
                                </td>
                                <td>{{ $voucher->usage_limit }}</td>
                                <td class="text-nowrap">{{ $voucher->start_date }}</td>
                                <td class="text-nowrap">{{ $voucher->end_date }}</td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="{{ route('vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-warning" title="Sửa voucher">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này không?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa voucher">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Không có voucher nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($vouchers->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="small text-muted">
            Hiển thị <strong>{{ $vouchers->firstItem() }}</strong> đến <strong>{{ $vouchers->lastItem() }}</strong> trong tổng <strong>{{ $vouchers->total() }}</strong> voucher
        </div>
        <div>
            {{ $vouchers->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection