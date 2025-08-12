@extends('admin.layouts.app')

@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="container-fluid px-3 px-md-4 px-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h2 class="mb-3 mb-md-0">
            <i class="bi bi-box-seam me-2"></i> Quản lý sản phẩm
        </h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </a>
            <a href="{{ route('products.trash') }}" class="btn btn-outline-secondary">
                <i class="bi bi-trash3-fill me-1"></i> Thùng rác
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" class="mb-3 mb-md-4">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        class="form-control border-primary"
                        placeholder="Tìm kiếm sản phẩm..."
                        value="{{ request('search') }}"
                        aria-label="Search products">
                    <button type="submit" class="btn btn-primary px-3 px-md-4">
                        <i class="bi bi-search me-1 d-none d-md-inline"></i> Tìm
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Mã</th>
                            <th>Kho</th>
                            <th>Màu</th>
                            <th>Size</th>
                            <th width="120">Ảnh</th>
                            <th width="120">Trạng thái</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->id }}</td>
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
                                <span class="badge bg-light text-dark mb-1">{{ $variant->size->name ?? 'N/A' }}</span>
                                <br>
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
                                <div class="d-flex gap-1">
                                    <a href="{{ route('products.edit', $p->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('products.show', $p->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Xóa"
                                            onclick="return confirm('Bạn chắc chắn muốn đưa sản phẩm này vào thùng rác không ?')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Hiển thị {{ $products->firstItem() }} đến {{ $products->lastItem() }} trong tổng số {{ $products->total() }} sản phẩm
                    </div>
                    <div class="">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table th {
        white-space: nowrap;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem;
    }

    .badge {
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-block;
    }

    .img-thumbnail {
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table th,
        .table td {
            min-width: 120px;
        }
    }
</style>

@endsection

@php
if (!function_exists('getContrastColor')) {
    function getContrastColor($hexColor) {
        $hexColor = ltrim($hexColor, '#');
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        return ($luminance > 0.5) ? '#000000' : '#ffffff';
    }
}
@endphp

