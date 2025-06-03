@extends('admin.layouts.app')


@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

      <div class="container">
        <h2>Danh mục</h2>
        <a href="{{route('categories.create')}}" class="btn btn-primary"><i class="bi bi-plus-circle"></i></a>
        <form method="GET" class="mb-4">
            <br>
            <div class="input-group shadow-sm rounded">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control border-primary" 
                    placeholder="🔍 Tìm kiếm danh mục..." 
                    value="{{ request('search') }}"
                    style="height: 48px;"
                >
                <button type="submit" class="btn btn-primary px-4" style="height: 48px;">
                    Tìm kiếm
                </button>
            </div>
        </form>
        <a href="{{ route('categories.trash') }}" class="btn btn-secondary mb-2">
            <i class="bi bi-trash3-fill"></i> Thùng rác
         </a>


        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Đường dẫn</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td>{{$c->id}}</td>
                        <td>{{$c->name}}</td>
                        <td>{{$c->slug}}</td>
                        <td>{{$c->status ? "hành động" : "tạm dừng"}}</td>

                        <td>
                            <a href="{{route('categories.edit', $c->id)}}" class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                            <form action="{{route('categories.destroy', $c->id)}}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Danh mục này sẽ chuyển vô thùng rác?')"><i class="bi bi-trash3"></i></button>
                            </form> 
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{$categories->links()}}
    </div>
    {{-- @if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK'
            });
        });
    </script> --}}
{{-- @endif --}}


@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
