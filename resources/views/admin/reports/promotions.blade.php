@extends('admin.layouts.app')

@section('title', 'Báo cáo khuyến mãi')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <select name="filter" class="form-control">
                            <option value="active" {{ request('filter') == 'active' ? 'selected' : '' }}>Đang chạy</option>
                            <option value="ended" {{ request('filter') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                        </select>
                    </div>

                    <div class="form-group mr-3" id="date-range" style="{{ request('filter') != 'custom' ? 'display:none' : '' }}">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', Carbon::now()->subDays(30)->toDateString()) }}">
                        <span class="mx-2">đến</span>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', Carbon::now()->toDateString()) }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.reports.export.promotions', request()->query()) }}" class="btn btn-success ml-2">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                </form>
            </div>
        </div>

        @if(request('filter') == 'active' || !request('filter'))
        <!-- Active Promotions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Chương trình khuyến mãi đang chạy</h5>
            </div>
            <div class="card-body">
                @if($activePromotions->isEmpty())
                <div class="alert alert-info">Không có chương trình khuyến mãi nào đang chạy</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên chương trình</th>
                                <th>Mã</th>
                                <th>Loại</th>
                                <th>Giảm giá</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Số đơn</th>
                                <th>Tổng giảm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activePromotions as $promotion)
                            <tr>
                                <td>{{ $promotion->name }}</td>
                                <td><span class="badge badge-info">{{ $promotion->code }}</span></td>
                                <td>{{ $promotion->type == 'shipping' ? 'Vận chuyển' : 'Sản phẩm' }}</td>
                                <td>
                                    @if($promotion->discount_type == 'amount')
                                    {{ number_format($promotion->discount_amount) }}đ
                                    @else
                                    {{ $promotion->discount_percent }}%
                                    @endif
                                </td>
                                <td>{{ $promotion->start_date->format('d/m/Y') }}</td>
                                <td>{{ $promotion->end_date->format('d/m/Y') }}</td>
                                <td>{{ $promotion->orders_count }}</td>
                                <td>{{ number_format($promotion->orders_sum_discount_amount) }}đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(request('filter') == 'ended')
        <!-- Ended Promotions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Chương trình khuyến mãi đã kết thúc</h5>
            </div>
            <div class="card-body">
                @if($endedPromotions->isEmpty())
                <div class="alert alert-info">Không có chương trình khuyến mãi nào đã kết thúc</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên chương trình</th>
                                <th>Mã</th>
                                <th>Loại</th>
                                <th>Giảm giá</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Số đơn</th>
                                <th>Tổng giảm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($endedPromotions as $promotion)
                            <tr>
                                <td>{{ $promotion->name }}</td>
                                <td><span class="badge badge-secondary">{{ $promotion->code }}</span></td>
                                <td>{{ $promotion->type == 'shipping' ? 'Vận chuyển' : 'Sản phẩm' }}</td>
                                <td>
                                    @if($promotion->discount_type == 'amount')
                                    {{ number_format($promotion->discount_amount) }}đ
                                    @else
                                    {{ $promotion->discount_percent }}%
                                    @endif
                                </td>
                                <td>{{ $promotion->start_date->format('d/m/Y') }}</td>
                                <td>{{ $promotion->end_date->format('d/m/Y') }}</td>
                                <td>{{ $promotion->orders_count }}</td>
                                <td>{{ number_format($promotion->orders_sum_discount_amount) }}đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(request('filter') == 'custom' && $promotionPerformance)
        <!-- Promotion Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Hiệu quả chương trình khuyến mãi ({{ $fromDate }} đến {{ $toDate }})</h5>
            </div>
            <div class="card-body">
                @if($promotionPerformance->isEmpty())
                <div class="alert alert-info">Không có dữ liệu hiệu quả khuyến mãi trong khoảng thời gian này</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên chương trình</th>
                                <th>Mã</th>
                                <th>Số đơn</th>
                                <th>Tổng doanh thu</th>
                                <th>Tổng giảm giá</th>
                                <th>Tỷ lệ sử dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotionPerformance as $promotion)
                            <tr>
                                <td>{{ $promotion->name }}</td>
                                <td><span class="badge badge-info">{{ $promotion->code }}</span></td>
                                <td>{{ $promotion->orders_count }}</td>
                                <td>{{ number_format($promotion->orders_sum_total) }}đ</td>
                                <td>{{ number_format($promotion->orders_sum_discount_amount) }}đ</td>
                                <td>
                                    @if($promotion->quantity)
                                    {{ round(($promotion->orders_count / $promotion->quantity) * 100, 2) }}%
                                    @else
                                    Unlimited
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide date range picker based on filter
    $('select[name="filter"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#date-range').show();
        } else {
            $('#date-range').hide();
        }
    });
});
</script>
@endpush