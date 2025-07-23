{{-- filepath: resources/views/admin/voucher/index.blade.php --}}
@extends('admin.layouts.app')

@section('content')
@if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Danh sách Voucher</h4>
        <a href="{{ route('vouchers.create') }}" class="btn btn-success">Thêm voucher mới</a>
    </div>
    <form action="{{ route('vouchers.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên voucher..." value="{{ request()->get('search') }}">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </div>
    </form>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Số lượng</th>
                        <th>Giảm giá</th>
                        
                        <th>Giới hạn/user</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @forelse($vouchers as $voucher)
                    {{-- <pre>{{ dd($voucher) }}</pre> --}}
                        <tr>
                            <td>{{ $voucher->id }}</td>
                            <td>{{ $voucher->code }}</td>
                            <td>{{ $voucher->name }}</td>
                            <td>
                                @if($voucher->type == 'shipping')
                                    <span class="badge bg-info">Giảm phí ship</span>
                                @else
                                    <span class="badge bg-primary">Giảm sản phẩm</span>
                                @endif
                            </td>
                           <td>
                                @if($voucher->quantity >= 1)
                                    <span class="badge bg-success">{{ $voucher->quantity }}</span>  
                                @else
                                    <span class="badge bg-danger">Hết voucher</span>
                                @endif
                            </td>
                            <td>
                                {{-- {{ $voucher->discount_type }} --}}
                                    @if($voucher->discount_type == 'percent')
                                        {{ (int)$voucher->discount_percent }}%
                                    @elseif($voucher->discount_type == 'amount')
                                        {{ number_format($voucher->discount_amount, 0, ',', '.') }} đ
                                    @else
                                        Không rõ
                                    @endif
                                    {{-- @php dd($voucher->discount_type); @endphp --}}
                                
                            </td>
                            <td>{{ $voucher->usage_limit }}</td>
                            <td>{{ $voucher->start_date }}</td>
                            <td>{{ $voucher->end_date }}</td>
                            <td>
                                <a href="{{ route('vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                                <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa voucher này không?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" >Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Chưa có voucher nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
     <!-- Pagination -->
    @if($vouchers->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Hiển thị {{ $vouchers->firstItem() }} đến {{ $vouchers->lastItem() }} trong tổng số {{ $vouchers->total() }} danh mục
        </div>
        <div class="">
            {{ $vouchers->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection