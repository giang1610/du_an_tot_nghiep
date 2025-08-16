{{-- filepath: resources/views/admin/users/show.blade.php --}}
@extends('admin.layouts.app')
@php
    $showEditForm = $errors->any();
@endphp
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-user me-2"></i>Chi tiết khách hàng
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    @if($user->img_thumbnail)
                        <img src="{{ asset('storage/' . $user->img_thumbnail) }}"
                             alt="{{ $user->name }}"
                             class="img-fluid rounded-circle mb-3"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <span class="avatar-title rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                              style="width: 120px; height: 120px; font-size: 2.5rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                    <div class="mt-2">
                        <span class="badge bg-{{ $user->is_admin ? 'danger' : 'secondary' }}">
                            {{ $user->is_admin ? 'Quản trị' : 'Người dùng' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-9">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted">Tên:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Điện thoại:</th>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Địa chỉ:</th>
                            <td>{{ $user->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Ngày tạo:</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                       
                    </table>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
                <button class="btn btn-warning" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#editUserForm"
                    aria-expanded="{{ $showEditForm ? 'true' : 'false' }}"
                    aria-controls="editUserForm">
                    <i class="fas fa-edit me-1"></i> Sửa
                </button>
            </div>
            <br>
            <div class="collapse mt-4 {{ $showEditForm ? 'show' : '' }}" id="editUserForm">
                <div class="card card-body border">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" >
                                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" >
                                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                                @error('address') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@if($showEditForm)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editForm = document.getElementById('editUserForm');
        if (editForm) {
            editForm.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>
@endif
@endsection