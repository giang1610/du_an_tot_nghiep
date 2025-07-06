@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <!-- Notification Alert -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/admin/products" class="btn btn-outline-danger">
                <i class="bi bi-arrow-left me-2"></i>
                Quay lại danh sách sản phẩm
            </a>
        </div>
        <div class="d-flex">
            <form action="{{ route('products.restoreAll') }}" method="POST" class="me-2">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Khôi phục tất cả sản phẩm?')">
                    <i class="bi bi-arrow-clockwise me-2"></i> Khôi phục tất cả
                </button>
            </form>

            <form action="{{ route('products.deleteAll') }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Xóa tất cả sản phẩm?')">
                    <i class="bi bi-trash-fill me-2"></i> Xóa tất cả
                </button>
            </form>
        </div>
    </div>

    <!-- Product Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-trash me-2"></i> Sản phẩm đã xóa</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Mã</th>
                            <th>Giá</th>
                            <th>Kho</th>
                            <th>Ảnh</th>
                            <th>Trạng thái</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td>{{$p->id}}</td>
                            <td>
                                <strong>{{$p->name}}</strong>
                                <div class="text-muted small">
                                    {{$p->category->name}}
                                </div>
                                <div class="mt-1">
                                    @foreach ($p->variants as $variant)
                                        <span class="badge bg-light text-dark me-1 mb-1">
                                            {{ $variant->color->name ?? '' }} / {{ $variant->size->name ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                    <span class="badge bg-light text-dark mb-1">{{ $variant->sku ?? 'N/A' }}</span>
                                @endforeach
                            </td>
                            <td>
                                {{ number_format($p->price_products) }}đ
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                    @if($variant->stock->quantity > 0)
                                        <span class="badge bg-success mb-1">{{ $variant->stock->quantity }}</span>
                                    @else
                                        <span class="badge bg-danger mb-1">Hết</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if ($p->thumbnail)
                                    <img src="{{ asset('storage/' . $p->thumbnail) }}" alt="Product Image"
                                         class="img-thumbnail" style="width: 80px; height: auto;">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge
                                    {{ $p->status === 1 ? 'bg-success' : '' }}
                                    {{ $p->status === 0 ? 'bg-secondary' : '' }}
                                    {{ $p->status === 2 ? 'bg-warning' : '' }}">
                                    @if ($p->status === 1)
                                        Hoạt động
                                    @elseif ($p->status === 0)
                                        Chưa xuất bản
                                    @else
                                        Tạm dừng
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <form action="{{ route('products.restore', $p->id) }}" method="POST" class="me-1">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Khôi phục">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('products.forceDelete', $p->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Xóa vĩnh viễn sản phẩm này?')"
                                                title="Xóa vĩnh viễn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Không có sản phẩm nào trong thùng rác</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection