@extends('admin.layouts.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
      <div class="container">
        <h2>Sản phẩm</h2>
        <a href="{{route('products.create')}}" class="btn btn-primary"><i class="bi bi-plus-circle"></i></a>
        <form method="GET" class="mb-4">
            <br>
            <div class="input-group shadow-sm rounded">
                <input
                    type="text"
                    name="search"
                    class="form-control border-primary"
                    placeholder="🔍 Tìm kiếm sản phẩm..."
                    value="{{ request('search') }}"
                    style="height: 48px;"
                >
                <button type="submit" class="btn btn-primary px-4" style="height: 48px;">
                    Tìm kiếm
                </button>
            </div>
        </form>
        <a href="{{ route('products.trash') }}" class="btn btn-secondary mb-2">
            <i class="bi bi-trash3-fill"></i> Thùng rác
         </a>


        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Danh mục</th>
                    <th>Tên sản phẩm</th>
                    <th>Đường dẫn</th>
                    <th>Giá sản phẩm</th>
                    {{-- <th>Giá biến thể</th> --}}
                    <th>Mã</th>
                    <th>Kho</th>
                    <th>Màu</th>
                    <th>Size</th>
                    <th>Ảnh sản phẩm</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>

                </tr>
            </thead>
            <tbody>


                @foreach ($products as $p)

                    <tr>
                        <td>{{$p->id}}</td>
                        <td>{{$p->category->name}}</td>
                        <td>{{$p->name}}</td>
                        <td>{{$p->slug}}</td>
                        <td>{{$p->price_products}}</td>
                         {{-- giá --}}
                        {{-- <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->price ?? 'Không có' }}</div>
                            @endforeach
                        </td> --}}
                         {{-- Mã --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->sku ?? 'Không có' }}</div>
                            @endforeach
                        </td>
                         {{-- Kho --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>
                                    @if( $variant->stock->quantity > 0)
                                       {{$variant->stock->quantity}}
                                    @else
                                        <span class="badge bg-danger mb-1">Hết</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                    @if($variant->color)
                                        <span class="badge bg-light text-dark mb-1" >
                                            {{ $variant->color->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark mb-1">N/A</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach ($p->variants as $variant)
                                    <span class="badge bg-light text-dark mb-1">{{ $variant->size->name ?? 'N/A' }}</span>
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
                                    <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Xóa"
                                                onclick="return confirm('Bạn chắc chắn muốn đưa sản phẩm này vào thùng rác?')">
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

            <div class="d-flex justify-content-center mt-3">
                {{ $products->links() }}
            </div>
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
        .table th, .table td {
            min-width: 120px;
        }
    }
</style>

@endsection

@php
function getContrastColor($hexColor) {
    // Remove # if present
    $hexColor = ltrim($hexColor, '#');

    // Convert to RGB
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    // Calculate luminance
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

    // Return black or white depending on luminance
    return ($luminance > 0.5) ? '#000000' : '#ffffff';
}
@endphp
