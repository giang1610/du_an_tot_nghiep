@extends('admin.layouts.app')
@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="container-fluid py-3 py-lg-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center flex-column flex-md-row">
                <h4 class="mb-2 mb-md-0">
                    <i class="fas fa-users me-2"></i>Quản lý nhân viên
                    <span class="badge bg-white text-primary ms-2">{{ $totalUsers }}</span>
                </h4>
                <!-- <div class="mt-2 mt-md-0">
                    <a href="#" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1 d-none d-md-inline"></i> Thêm mới
                    </a>
                </div> -->
            </div>
        </div>

        <div class="card-body">
            {{-- Form tìm kiếm --}}
            <form method="GET" action="{{ route('users.staff') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tìm kiếm theo tên, email..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1 d-none d-md-inline"></i> 
                            <span class="d-inline d-md-none">Tìm</span>
                            <span class="d-none d-md-inline">Tìm kiếm</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="{{ route('users.createStaff') }}" class="btn btn-success w-50">
                            <i class="bi bi-plus-circle me-1"></i> 
                            <span class="d-inline d-md-none">Thêm</span>
                          
                        </a>

                   
                </div>
            </form>

            {{-- Bảng danh sách --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle d-none d-md-table">
                    <thead class="table-light">
                        <tr>
                            <th width="80" class="text-center">ID</th>
                            <th>Tên nhân viên</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Ảnh đại diện</th>
                            <th width="180" class="text-center">Ngày tạo</th>
                            <th class="text-center">Thao tác</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="{{ $loop->odd ? 'table-active' : '' }}">
                                <td class="text-center fw-bold">{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- <div class="avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        </div> -->
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">@if($user->is_admin) Quản trị @else Người dùng @endif</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->address }}</td>
                                <td>
                                @if($user->img_thumbnail)
                                    <img src="{{ asset('storage/' . $user->img_thumbnail) }}" 
                                        alt="{{ $user->name }}" class="img-fluid rounded-circle" 
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <span class="avatar-title rounded-circle bg-secondary text-white" 
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                @endif
                            </td>

                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </span>
                                </td>
                                {{-- <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                       
                                    </div>
                                </td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">Không tìm thấy nhân viên nào</h4>
                                        @if($search)
                                            <p class="text-muted">Thử lại với từ khóa tìm kiếm khác</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Mobile view
                <div class="d-block d-md-none">
                    @forelse ($users as $user)
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <!-- <small class="text-muted">@if($user->role == 1) Quản trị @else Người dùng @endif</small> -->
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        #{{ $user->id }}
                                    </span>
                                </div>
                                
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </small>
                                    <!-- <div class="btn-group btn-group-sm">
                                        <a href="#" class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-outline-success btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Không tìm thấy người dùng nào</h4>
                            @if($search)
                                <p class="text-muted">Thử lại với từ khóa tìm kiếm khác</p>
                            @endif
                        </div>
                    @endforelse
                </div> --}}
            </div>

            {{-- Phân trang --}}
            @if($users->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                    <div class="text-muted mb-2 mb-md-0 text-center text-md-start">
                        Hiển thị <b>{{ $users->firstItem() }}</b> đến <b>{{ $users->lastItem() }}</b> 
                        trong tổng số <b>{{ $users->total() }}</b> người dùng
                    </div>
                    <div>
                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-title {
        font-weight: 600;
        font-size: 1.1rem;
    }
    .empty-state {
        padding: 2rem 0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .card {
        border-radius: 0.5rem;
    }
    @media (max-width: 767.98px) {
        .card-header h4 {
            font-size: 1.25rem;
        }
        .empty-state i {
            font-size: 2rem;
        }
        .empty-state h4 {
            font-size: 1.1rem;
        }
    }
</style>
@endsection