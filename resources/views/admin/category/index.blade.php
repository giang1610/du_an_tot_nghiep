@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Notification Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0">
            <i class="bi bi-tags me-2"></i>Quản lý Danh mục
        </h2>
        <div>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </a>
            <a href="{{ route('categories.trash') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-trash3-fill me-1"></i> Thùng rác
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="mb-0">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        class="form-control border-primary"
                        placeholder="Tìm kiếm danh mục..."
                        value="{{ request('search') }}"
                    >
                    <button type="submit" class="btn btn-primary px-4">
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">ID</th>
                            <th>Tên danh mục</th>
                            <th>Đường dẫn</th>
                            <th width="120">Trạng thái</th>
                            <th width="150" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>
                                <p>{{ $c->name }}</p>
                            </td>
                            <td class="text-muted">{{ $c->slug }}</td>
                            <td>
                                <span class="badge {{ $c->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $c->status ? "Hoạt động" : "Tạm dừng" }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('categories.edit', $c->id) }}" 
                                       class="btn btn-sm btn-outline-primary me-2"
                                       title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('categories.show', $c->id) }}" 
                                       class="btn btn-sm btn-outline-info me-2"
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('categories.destroy', $c->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Danh mục này sẽ được chuyển vào thùng rác?')"
                                                title="Xóa">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-folder-x me-2"></i>Không tìm thấy danh mục nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Hiển thị {{ $categories->firstItem() }} đến {{ $categories->lastItem() }} trong tổng số {{ $categories->total() }} danh mục
        </div>
        <div class="">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection
