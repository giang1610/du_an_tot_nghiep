@extends('admin.layouts.app')

@section('content')
  <a href="/categories"><i class="bi bi-arrow-left" style="font-size: 1.5rem; color: red;"></i>

        Quay lại danh sách danh mục

</a>
      <div class="container">
         <h1 class="h4 mb-4">Thêm danh mục mới</h1>
         {{-- <a href="{{ route('categories.create') }}" class="btn btn-primary mb-3">Thêm danh mục</a> --}}
   
         @if (session('error'))
            <div class="mb-0 alert alert-success">
                {{session('error')}}
            </div>
        @endif
               <form action="{{route('categories.update', $category->id)}}" method="post">
                @csrf
                 @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label h4">Tên danh mục</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên danh mục" value="{{$category->name}}">
                    @error('name')
                        <div class="text-danger">{{$message}}</div>
                    @enderror
                </div>
                <div class="mb-3">
                <label for="slug" class="form-label h4">Đường dẫn danh mục</label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="Đường dẫn danh mục tự động tạo" value="{{$category->slug}}">
                @error('slug')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
                <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select" required>
                    <option value="1" selected>Hoạt động</option>
                    <option value="0">Tạm dừng</option>
                </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Sửa</button>
                </div>
                </form>

        
   
        
      </div>
      <script>
    // Hàm chuyển tiếng Việt có dấu thành không dấu và chuyển khoảng trắng thành dấu gạch ngang
        function slugify(text) {
            return text.toString().normalize('NFD') // tách dấu
                .replace(/[\u0300-\u036f]/g, '') // loại bỏ dấu
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '') // bỏ ký tự đặc biệt
                .replace(/\s+/g, '-') // thay khoảng trắng thành dấu -
                .replace(/-+/g, '-'); // loại bỏ dấu - liên tiếp
        }

        document.getElementById('name').addEventListener('input', function() {
            const nameValue = this.value;
            const slugValue = slugify(nameValue);
            document.getElementById('slug').value = slugValue;
        });
    </script>
@endsection
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">