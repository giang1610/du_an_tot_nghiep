@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/categories" class="btn btn-outline-danger">
            <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách danh mục
        </a>
        <div class="d-flex">
            <form action="{{ route('categories.restoreAll') }}" method="POST" class="me-2">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Khôi phục tất cả danh mục?')">
                    <i class="bi bi-arrow-clockwise me-2"></i>Khôi phục tất cả
                </button>
            </form>

            {{-- <form action="{{ route('categories.deleteAll') }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Xóa tất cả danh mục?')">
                    <i class="bi bi-trash-fill me-2"></i>Xóa tất cả
                </button>
            </form> --}}
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-trash3 me-2"></i>Danh mục đã xóa</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100">ID</th>
                            <th>Tên danh mục</th>
                            <th>Trạng thái</th>
                            <th>số ngày còn trong thùng rác</th>
                            <th width="150" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                @if($category->deleted_at)
                                    <div class="text-muted small">
                                        Xóa ngày: {{ $category->deleted_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($category->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Không hoạt động</span>
                                @endif
                            </td>
                            <td>
                                @if($category->deleted_at)
                                    @php
                                        $daysLeft = 20 - $category->deleted_at->diffInDays(now());
                                        $daysLeft = $daysLeft > 0 ? $daysLeft : 0;
                                    @endphp
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
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <form action="{{ route('categories.restore', $category->id) }}" method="POST" class="me-2">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Khôi phục">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>

                                    {{-- <form action="{{ route('categories.forceDelete', $category->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Xóa vĩnh viễn danh mục này?')"
                                                title="Xóa vĩnh viễn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form> --}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <i class="bi bi-trash3 me-2"></i>Thùng rác trống
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection