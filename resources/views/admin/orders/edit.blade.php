@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <nav aria-label="breadcrumb" class="mt-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin" style="text-decoration: none">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" style="text-decoration: none">Danh sách đơn hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">xử lý các đơn yêu cầu hoàn hàng</li>
            </ol>
        </nav>

        {{-- Form xử lý yêu cầu hoàn đơn --}}
        @if($order->status === 'return_requested')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Xử lý yêu cầu hoàn đơn</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do khách hàng:</label>
                        <div class="border rounded p-2 bg-light">
                            {{ $order->return_reason ?? '— Không có lý do —' }}
                        </div>
                        @if($order->return_media)
                            <div>
                                <strong>File minh chứng:</strong>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @php
                                        $mediaList = [];
                                        try {
                                            $mediaList = is_array($order->return_media)
                                                ? $order->return_media
                                                : json_decode($order->return_media, true) ?? [];
                                        } catch (\Throwable $e) {
                                            $mediaList = [];
                                        }
                                    @endphp
                                    @foreach($mediaList as $media)
                                        @if(Str::endsWith($media, ['.jpg','.jpeg','.png']))
                                            <img src="{{ asset('storage/' . $media) }}" alt="Ảnh lỗi" width="150" class="rounded border" />
                                        @elseif(Str::endsWith($media, ['.mp4','.mov']))
                                            <video src="{{ asset('storage/' . $media) }}" controls width="200" class="rounded border"></video>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <form action="{{ route('orders.handleReturn', $order->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="action" class="form-label">Hành động:</label>
                            <select name="action" id="action" class="form-select">
                                <option value="accept">Đồng ý hoàn hàng</option>
                                <option value="reject">Từ chối hoàn hàng</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="note_admin" class="form-label">Ghi chú admin:</label>
                            <textarea name="note_admin" id="note_admin" rows="3" class="form-control">{{ $order->note_admin }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Xử lý</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Form cập nhật trạng thái đơn hàng --}}
        {{-- <form action="{{ route('orders.update', $order->id) }}" method="POST" class="bg-white rounded-3 shadow p-4">
            @csrf
            @method('PUT')

            <!-- Header với nút quay lại -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <a href="/admin/orders" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Quay lại danh sách
                    </a>
                </div>
                <h2 class="mb-0 text-primary">Cập nhật trạng thái đơn hàng</h2>
            </div>

            <!-- Thông tin đơn hàng -->
            <!-- ...giữ nguyên phần thông tin đơn hàng... -->

            <!-- Trạng thái đơn hàng -->
            @php
                $statusOptions = [
                    'cancelled' => 'Hủy đơn hàng',
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'picking' => 'Đang lấy hàng',
                    'shipping' => 'Đang giao hàng',
                    'shipped' => 'Đã giao hàng',
                    'return_requested' => 'Yêu cầu hoàn hàng',
                    'delivered' => 'Đã nhận hàng',
                    'returned' => 'Đồng ý hoàn hàng',
                    'restocked' => 'Hàng đã trả về kho',
                    'completed' => 'Đơn hàng hoàn thành',
                    'failed_1' => 'Giao hàng thất bại lần 1',
                    'failed_2' => 'Giao hàng thất bại lần 2',
                    'failed' => 'Giao hàng thất bại',
                    'shipper_en_route' => 'Shipper đang đến lấy hàng',

                ];
                $statusFlow = ['pending', 'processing', 'picking', 'shipping', 'shipped'];
                $currentStatus = old('status', $order->status ?? 'pending');
                $currentIndex = array_search($currentStatus, $statusFlow);
                $nextStatus = $statusFlow[$currentIndex + 1] ?? null;
            @endphp

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Cập nhật trạng thái</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái hiện tại</label>
                        <input type="text" class="form-control bg-light fw-bold" value="{{ $statusOptions[$currentStatus] }}" readonly>
                    </div>


                    <div class="mb-3">
                        @if ($currentStatus == 'shipped' && !in_array($order->status, ['return_requested', 'returned']))
                            <input type="text" class="form-control bg-light fw-bold" value="{{ $statusOptions[$currentStatus] }}" readonly>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Đơn hàng đã nhận hàng. Nếu khách không yêu cầu trả hàng, trạng thái sẽ tự động chuyển sang
                                <b>Hoàn thành</b> sau 3 ngày.
                            </div>
                        @else
                            <label class="form-label">Chọn trạng thái mới</label>
                            <select name="status" class="form-select" required>
                                 @if ($currentStatus === 'returned')
                                     <option value="{{ $currentStatus }}" selected disabled>
                                    {{ $statusOptions[$currentStatus] }} (hiện tại)
                                     </option>
                                    <option value="shipper_en_route">{{ $statusOptions['shipper_en_route'] }}</option>
                                @elseif ($currentStatus === 'shipper_en_route')
                                    <option value="{{ $currentStatus }}" selected disabled>
                                        {{ $statusOptions[$currentStatus] }} (hiện tại)
                                    </option>
                                    <option value="restocked">{{ $statusOptions['restocked'] }}</option>
                                @else
                                @if (!in_array($currentStatus, ['shipped', 'completed', 'failed', 'returned', 'return_requested','failed_1', 'failed_2', 'returning']))
                                    <option value="cancelled" {{ $currentStatus == 'cancelled' ? 'selected' : '' }}>
                                        {{ $statusOptions['cancelled'] }}
                                    </option>
                                @endif
                                <option value="{{ $currentStatus }}" selected disabled>
                                    {{ $statusOptions[$currentStatus] }} (hiện tại)
                                </option>
                                {{-- @if ($nextStatus)
                                    <option value="{{ $nextStatus }}">
                                        {{ $statusOptions[$nextStatus] }}
                                    </option>
                                @endif --}}
                                {{-- @if (in_array($currentStatus, ['cancelled', 'shipped', 'completed']))
                                <div class="alert alert-warning mt-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Đơn hàng đã ở trạng thái <b>{{ $statusOptions[$currentStatus] }}</b>, không thể đổi trạng thái nữa.
                                </div>
                                @endif
                                 @if ($currentStatus === 'shipping')
                                <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                <option value="failed_1">Giao hàng thất bại lần 1</option>
                                @elseif ($currentStatus === 'failed_1')
                                    <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                    <option value="failed_2">Giao hàng thất bại lần 2</option>
                                @elseif ($currentStatus === 'failed_2')
                                    <option value="shipped">{{ $statusOptions['shipped'] }}</option>
                                    <option value="failed">{{ $statusOptions['failed'] }}</option>
                                @elseif ($currentStatus === 'failed')
                                     <option value="restocked">Hàng đã trả về kho</option>
                                @elseif ($nextStatus)
                                    <option value="{{ $nextStatus }}">{{ $statusOptions[$nextStatus] }}</option>
                                @endif
                                @if ($currentStatus === 'returned')
                                    <option value="shipper_en_route">Shipped đang lấy hàng</option>
                                @endif
                                 @if ($currentStatus === 'shipper_en_route')
                                    <option value="restocked">Hàng đã trả về kho</option>
                                @endif

                            @endif

                            </select>
                    </div>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Chỉ có thể chuyển sang trạng thái kế tiếp trong quy trình hoặc hủy đơn hàng.
                        </div>
                        @endif

                </div>
            </div>

            @if (!in_array($currentStatus, ['shipped','cancelled','completed','restocked']) && $order->status !== 'return_requested')
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check-circle me-2"></i>Cập nhật trạng thái
                    </button>
                </div>
            @endif
        </form> --}} 
    </div>

    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }
        .form-control:read-only,
        .form-select:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
@endsection