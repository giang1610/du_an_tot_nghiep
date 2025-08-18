{{-- filepath: resources/views/admin/users/createStaff.blade.php --}}
@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-user-plus me-2"></i>Thêm nhân viên mới
            </h4>
        </div>
        <div class="card-body">
            {{-- @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif --}}

            <form action="{{ route('users.createStaff') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên nhân viên</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" >
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="off">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password" value="{{ old('password') }}">
                        @error('password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" >
                        @error('password_confirmation')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" >
                        @error('phone')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}" >
                        @error('address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ảnh đại diện (tùy chọn)</label>
                    <div class="mt-2">
                        <img id="img_thumbnail_preview" src="#" alt="Preview" style="display:none; max-width:120px; max-height:120px; border-radius:8px;"/>
                    </div> <br>
                    <input type="file" name="img_thumbnail" class="form-control" id="img_thumbnail_input">
                    @error('img_thumbnail')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    
                </div>
                @push('scripts')
                <script>
                document.getElementById('img_thumbnail_input').addEventListener('change', function(event) {
                    const [file] = event.target.files;
                    const preview = document.getElementById('img_thumbnail_preview');
                    if (file) {
                        preview.src = URL.createObjectURL(file);
                        preview.style.display = 'block';
                    } else {
                        preview.src = '#';
                        preview.style.display = 'none';
                    }
                });
                </script>
                @endpush
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Thêm nhân viên
                    </button>
                    <a href="{{ route('users.staff') }}" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection