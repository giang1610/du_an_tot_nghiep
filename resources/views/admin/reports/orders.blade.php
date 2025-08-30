@extends('admin.layouts.app')

@section('title', 'Báo cáo đơn hàng')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <select name="filter" class="form-control">
                            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="7days" {{ request('filter') == '7days' ? 'selected' : '' }}>7 ngày</option>
                            <option value="30days" {{ request('filter') == '30days' ? 'selected' : '' }}>30 ngày</option>
                            <option value="thismonth" {{ request('filter') == 'thismonth' ? 'selected' : '' }}>Tháng này</option>
                            <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>1 năm</option>
                            <option value="thisyear" {{ request('filter') == 'thisyear' ? 'selected' : '' }}>Năm nay</option>
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                        </select>
                    </div>

                    <div class="form-group mr-3" id="date-range" style="{{ request('filter') != 'custom' ? 'display:none' : '' }}">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', Carbon::now()->subDays(30)->toDateString()) }}">
                        <span class="mx-2">đến</span>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', Carbon::now()->toDateString()) }}">
                    </div>

                    <div class="form-group mr-3">
                        <select name="status" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Đã trả hàng</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Lọc</button>
                    @if(request('status'))
                    <a href="{{ route('admin.reports.export.orders', request()->query()) }}" class="btn btn-success ml-2">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Order Status Overview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Tổng quan trạng thái đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="orderStatusChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <th>Số lượng</th>
                                        <th>Tỷ lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderStatusPercentage as $status)
                                    <tr>
                                        <td>{{ $status->status }}</td>
                                        <td>{{ $status->count }}</td>
                                        <td>{{ $status->percentage }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtered Orders -->
        @if(request('status') && $filteredOrders)
        <div class="card mb-4">
            <div class="card-header">
                <h5>Chi tiết đơn hàng {{ request('status') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Số lượng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($filteredOrders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->user->name }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->items->sum('quantity') }}</td>
                                <td>{{ number_format($order->total) }}đ</td>
                                <td>
                                    <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ $orderStatusStats->firstWhere('status', $order->status)->status ?? $order->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($orderStatusPercentage->pluck('status')),
            datasets: [{
                data: @json($orderStatusPercentage->pluck('percentage')),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}%`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush