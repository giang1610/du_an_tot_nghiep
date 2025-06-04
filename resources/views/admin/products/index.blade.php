@extends('admin.layouts.app')


@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
        {{-- <a href="{{ route('categories.trash') }}" class="btn btn-secondary mb-2">
            <i class="bi bi-trash3-fill"></i> Thùng rác
         </a> --}}


        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Danh mục</th>
                    <th>Tên</th>
                    <th>Đường dẫn</th>
                    <th>Giá</th>
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
                         {{-- giá --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->price ?? 'Không có' }}</div>
                            @endforeach
                        </td>
                         {{-- Mã --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->sku ?? 'Không có' }}</div>
                            @endforeach
                        </td>
                        {{-- <td>
                            @php
                                $stock = optional($p->variants->first())->stock;
                            @endphp

                            {{ $stock == 0 ? 'Còn hàng' : 'Hết hàng' }}
                        </td> --}}
                         {{-- Mã --}}
                        <td>
                            @foreach ($p->variants as $variant)
                                <div>{{ $variant->stock == 0 ? 'còn hàng' : 'Hết hàng'  }}</div>
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
                                hành động
                            @elseif ($p->status === 0)
                                Chưa xuất bản
                            @else
                               tạm dừng
                            @endif
                        </td>
                        <td>
                           <a href="{{route('products.edit', $p->id)}}" class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                             <form action="{{route('products.destroy', $p->id)}}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?')"><i class="bi bi-trash3"></i></button>
                            </form> 
                        </td>
                    </tr>
                @endforeach
                
            </tbody>
        </table>
        {{$products->links()}}
    </div>
  


@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
