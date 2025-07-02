@extends('admin.layouts.app')
@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<a href="/admin/products"><i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i>

        Quay lại danh sách sản phẩm

</a><br>
<form action="{{ route('products.restoreAll') }}" method="POST" style="display: inline-block;">
    @csrf
    <button type="submit" class="btn btn-success mb-3" onclick="return confirm('Khôi phục tất cả sản phẩm?')">
        <i class="bi bi-arrow-clockwise"></i> Khôi phục tất cả
    </button>
</form>

<form action="{{ route('products.deleteAll') }}" method="POST" style="display: inline-block;">
      @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger mb-3" onclick="return confirm('Xóa tất cả sản phẩm?')">
        <i class="bi bi-trash-fill"></i> Xóa tất cả
    </button>
</form>

<table class="table">
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
    @foreach($products as $p)
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
                                        Không có hàng
                                    @endif
                                </div>
                            @endforeach
                        </td>

                       {{-- Màu --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->color->name ?? 'Không có' }}</div>
                            @endforeach
                        </td>

                        {{-- Size --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->size->name ?? 'Không có' }}</div>
                            @endforeach
                        </td>
                        <td>


                            @if ($p->thumbnail)
                                <img src="{{ asset('storage/' . $p->thumbnail) }}" alt="Product Image" style="width: 100px; height: auto;">

                            @else
                                <span>Không có ảnh</span>
                            @endif
                        </td>
                        <td>
                            @if ($p->status === 1)
                                Hoạt động
                            @elseif ($p->status === 0)
                                Chưa xuất bản
                            @else
                                Tạm dừng
                            @endif
                        </td>
            <td>
                <form action="{{ route('products.restore', $p->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
                </form>

                <form action="{{ route('products.forceDelete', $p->id) }}" method="POST" style="display:inline-block;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa vĩnh viễn?')"><i class="bi bi-trash-fill"></i></button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">