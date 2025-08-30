@extends('admin.layouts.app')
@section('content')
<div class="container-fluid px-4">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
     <br>
    <h4 class="mb-0 fw-bold">💬 Quản lý Bình luận</h4> <br>
    <form method="GET" class="mb-3 mb-md-4">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        class="form-control border-primary"
                        placeholder="Tìm kiếm bình luận..."
                        value="{{ request('search') }}"
                        aria-label="Search riviews">
                    <button type="submit" class="btn btn-primary px-3 px-md-4">
                        <i class="bi bi-search me-1 d-none d-md-inline"></i> Tìm
                    </button>
                </div>
        </form>
    <div class="card shadow-sm">
        
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Ảnh/Video</th>
                        <th>Số sao</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review->id }}</td>
                        <td>{{ $review->user->name ?? 'Không có khách hàng' }}</td>
                       <td>
                            <div style="display: flex; align-items: flex-start; gap: 8px;">
                                <img src="{{ asset('storage/' . (
                                    $review->productVariant->image 
                                    ?? $review->product->image 
                                    ?? 'images/default-product.jpg'
                                )) }}" 
                                width="40" height="40" class="rounded" style="object-fit: cover;">

                                <div>
                                    <div>{{ $review->product->name ?? 'Không có biến thể' }}</div>
                                    @if($review->productVariant)
                                        <div class="text-muted small">
                                            {{ $review->productVariant->color->name ?? '' }} -
                                            {{ $review->productVariant->size->name ?? '' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            @php
                                $mediaList = [];
                                try {
                                    $mediaList = is_array($review->media)
                                        ? $review->media
                                        : (is_string($review->media) && Str::startsWith($review->media, '[')
                                            ? json_decode($review->media, true)
                                            : ($review->media ? [$review->media] : [])
                                        );
                                } catch (\Throwable $e) {
                                    $mediaList = [];
                                }
                            @endphp
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($mediaList as $media)
                                    @if(Str::endsWith($media, ['.jpg','.jpeg','.png','.gif']))
                                        <img src="{{ asset('storage/' . ltrim($media, '/')) }}" alt="Ảnh review" width="100" class="rounded border" />
                                    @elseif(Str::endsWith($media, ['.mp4','.mov','.avi']))
                                        <video src="{{ asset('storage/' . ltrim($media, '/')) }}" controls width="120" class="rounded border"></video>
                                    @else
                                        <span class="text-muted">File không xác định</span>
                                    @endif
                                @endforeach
                                @if(empty($mediaList))
                                    <span class="text-muted">Không có media</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> {{ $review->rating }}
                            </span>
                        </td>
                        <td>{{ $review->content }}</td>
                        <td>
                            <form action="{{ route('reviews.update', $review->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                 <button type="submit" class="btn btn-sm {{ $review->status ? 'btn-warning' : 'btn-success' }}" title="{{ $review->status ? 'Ẩn đánh giá' : 'Hiện đánh giá' }}">
                                    <i class="fas {{ $review->status ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa đánh giá này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger ms-1"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Chưa có đánh giá nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Hiển thị {{ $reviews->firstItem() }} đến {{ $reviews->lastItem() }} trong tổng số {{ $reviews->total() }} bình luận
                    </div>
                    <div class="">
                        {{ $reviews->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
    </div>
</div>
@endsection