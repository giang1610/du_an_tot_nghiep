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

            {{-- <form action="{{ route('products.deleteAll') }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Xóa tất cả sản phẩm?')">
                    <i class="bi bi-trash-fill me-2"></i> Xóa tất cả
                </button>
            </form> --}}
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
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Mã</th>
                            <th>Kho</th>
                            <th>Màu</th>
                            <th>Size</th>
                            <th width="120">Ảnh</th>
                            <th width="120">Trạng thái</th>
                            <th>Số ngày còn trong thùng rác</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td>{{$p->id}}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $p->name }}</span>
                                    <small class="text-muted">{{ $p->category->name ?? 'Không có danh mục' }}</small>
                                </div>
                            </td>
                            <td class="fw-medium text-nowrap">
                                {{ number_format($p->price_products, 0, ',', '.') }}₫
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                <span class="badge bg-light text-dark mb-1">{{ $variant->sku ?? 'N/A' }}</span>
                                <br>
                                @endforeach
                            </td>
                            
                                <td>
                                    @foreach ($p->variants as $variant)
                                <span
                                    id="stock-badge-{{ $variant->id }}"
                                    class="badge {{ optional($variant->stock)->quantity > 0 ? 'bg-success' : 'bg-danger' }} mb-1">
                                    {{ optional($variant->stock)->quantity > 0 ? optional($variant->stock)->quantity : 'Hết' }}
                                </span>
                                <br>
                                @endforeach
                                </td>
                            
                            <td>
                                @foreach ($p->variants as $variant)
                                @if($variant->color)
                                <span class="badge bg-light text-dark mb-1">
                                    {{ $variant->color->name }}
                                </span>
                                <br>
                                @else
                                <span class="badge bg-light text-dark mb-1">N/A</span>
                                @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                @if($variant->color)
                                <span class="badge bg-light text-dark mb-1">
                                    {{ $variant->size->name }}
                                </span>
                                <br>
                                @else
                                <span class="badge bg-light text-dark mb-1">N/A</span>
                                @endif
                                @endforeach
                            </td>
                            <td>
                                @if ($p->thumbnail)
                                <img src="{{ asset('storage/' . $p->thumbnail) }}"
                                    alt="{{ $p->name }}"
                                    class="img-thumbnail"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                <span class="badge bg-light text-dark">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($p->status === 1)
                                <span class="badge bg-success">Hoạt động</span>
                                @elseif ($p->status === 0)
                                <span class="badge bg-warning text-dark">Chưa xuất bản</span>
                                @else
                                <span class="badge bg-secondary">Tạm dừng</span>
                                @endif
                            </td>
                             <td>
                                @if($p->deleted_at)
                                {{-- tính ngày xóa --}}
                                    @php
                                        $daysLeft = 20 - $p->deleted_at->diffInDays(now());
                                        $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                                    @endphp
                                    {{-- hiển thị số ngày còn lại --}}
                                    <span class="badge {{ $daysLeft > 3 ? 'bg-info' : 'bg-danger' }}">
                                        {{ $daysLeft }} ngày
                                    </span>
                                    <div class="text-muted small">
                                        (Tự động xóa sau {{ $daysLeft }} ngày)
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Không xác định</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex">
                                    <form action="{{ route('products.restore', $p->id) }}" method="POST" class="me-1">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Khôi phục">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>

                                    {{-- <form action="{{ route('products.forceDelete', $p->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Xóa vĩnh viễn sản phẩm này?')"
                                                title="Xóa vĩnh viễn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form> --}}
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