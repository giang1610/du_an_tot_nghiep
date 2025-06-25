@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="text-2xl font-semibold mb-4">Thông tin tài khoản</h2>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success mb-3">
            Cập nhật thông tin thành công.
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="name" class="form-label">Họ và tên</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required>

            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                   class="form-control @error('email') is-invalid @enderror" required>

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

         <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Lưu thay đổi
            </button>
    </form>

    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xoá vĩnh viễn tài khoản này? Hành động này không thể hoàn tác.')">
                    <i class="fas fa-trash-alt me-2"></i>Xoá tài khoản
        </button>
    </form>
</div>
@endsection
