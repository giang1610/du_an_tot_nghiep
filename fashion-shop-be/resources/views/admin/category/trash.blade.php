@extends('admin.layouts.app')
@section('content')
<a href="/categories"><i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i>

        Quay lại danh sách danh mục

</a><br>
<form action="{{ route('categories.restoreAll') }}" method="POST" style="display: inline-block;">
    @csrf
    <button type="submit" class="btn btn-success mb-3" onclick="return confirm('Khôi phục tất cả danh mục?')">
        <i class="bi bi-arrow-clockwise"></i> Khôi phục tất cả
    </button>
</form>

<form action="{{ route('categories.deleteAll') }}" method="POST" style="display: inline-block;">
      @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger mb-3" onclick="return confirm('Xóa tất cả danh mục?')">
        <i class="bi bi-trash-fill"></i> Xóa tất cả
    </button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>ID</th><th>Tên</th><th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
    @foreach($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>
                <form action="{{ route('categories.restore', $category->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
                </form>

                <form action="{{ route('categories.forceDelete', $category->id) }}" method="POST" style="display:inline-block;">
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