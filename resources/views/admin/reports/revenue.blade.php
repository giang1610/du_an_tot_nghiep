@extends('admin.layouts.app')
@section('title', 'Báo cáo doanh thu')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form action="{{ route('admin.reports.revenue') }}" method="GET" class="d-flex align-items-center">
                <div class="form-group mr-3">
                    <select name="filter" class="form-control">
                        <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                        <option value="7days" {{ request('filter') == '7days' ? 'selected' : '' }}>7 ngày</option>
                        <option value="30days" {{ request('filter') == '30days' ? 'selected' : '' }}>30 ngày</option>
                        <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="1year" {{ request('filter') == '1year' ? 'selected' : '' }}>1 năm</option>
                        <option value="this_year" {{ request('filter') == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                        <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                    </select>
                </div>

                <div class="form-group mr-3" id="date-range" style="{{ request('filter') != 'custom' ? 'display:none' : '' }}">
                    <input type="date" name="from_date" class="form-control" 
                           value="{{ request('from_date', Carbon\Carbon::now()->subDays(30)->toDateString()) }}">
                    <span class="mx-2">đến</span>
                    <input type="date" name="to_date" class="form-control" 
                           value="{{ request('to_date', Carbon\Carbon::now()->toDateString()) }}">
                </div>

                <div class="form-group mr-3">
                    <select name="compare_with" class="form-control">
                        <option value="">-- So sánh với --</option>
                        <option value="previous_period" {{ request('compare_with') == 'previous_period' ? 'selected' : '' }}>Kỳ trước</option>
                        <option value="previous_week" {{ request('compare_with') == 'previous_week' ? 'selected' : '' }}>Tuần trước</option>
                        <option value="previous_month" {{ request('compare_with') == 'previous_month' ? 'selected' : '' }}>Tháng trước</option>
                        <option value="previous_year" {{ request('compare_with') == 'previous_year' ? 'selected' : '' }}>Năm trước</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('reports.revenue.export', request()->query()) }}" class="btn btn-success ml-2">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </form>
        </div>
    </div>
</div>

@if ($isEmpty)
    <div class="alert alert-info">Không có dữ liệu trong khoảng thời gian đã chọn</div>
@else
    <div class="row">
        <div class="col-md-3">
            <div class="card card-body bg-primary text-white mb-4">
                <h5 class="card-title">Tổng doanh thu</h5>
                <h3 class="card-text">{{ number_format($summary->completed_revenue) }}đ</h3>
                @if (isset($compareData) && $compareData['summary']->completed_revenue > 0)
                    <small class="text-white">
                        @php
                            $change = (($summary->completed_revenue - $compareData['summary']->completed_revenue) / 
                                     $compareData['summary']->completed_revenue) * 100;
                        @endphp
                        {{ $change >= 0 ? '↑' : '↓' }} {{ abs(round($change, 2)) }}%
                    </small>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-body bg-success text-white mb-4">
                <h5 class="card-title">Doanh thu thuần</h5>
                <h3 class="card-text">{{ number_format($summary->gross_profit ?? 0) }}đ</h3>
                @if (isset($compareData) && isset($compareData['summary']->gross_profit) && $compareData['summary']->gross_profit > 0)
                    <small class="text-white">
                        @php
                            $change = (($summary->gross_profit - $compareData['summary']->gross_profit) / 
                                     $compareData['summary']->gross_profit) * 100;
                        @endphp
                        {{ $change >= 0 ? '↑' : '↓' }} {{ abs(round($change, 2)) }}%
                    </small>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-body bg-info text-white mb-4">
                <h5 class="card-title">Đơn hàng</h5>
                <h3 class="card-text">{{ $summary->completed_orders }}</h3>
                @if (isset($compareData) && $compareData['summary']->completed_orders > 0)
                    <small class="text-white">
                        @php
                            $change = (($summary->completed_orders - $compareData['summary']->completed_orders) / 
                                     $compareData['summary']->completed_orders) * 100;
                        @endphp
                        {{ $change >= 0 ? '↑' : '↓' }} {{ abs(round($change, 2)) }}%
                    </small>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-body bg-warning text-white mb-4">
                <h5 class="card-title">Giá trị trung bình</h5>
                <h3 class="card-text">{{ number_format($summary->avg_order_value) }}đ</h3>
                @if (isset($compareData) && $compareData['summary']->avg_order_value > 0)
                    <small class="text-white">
                        @php
                            $change = (($summary->avg_order_value - $compareData['summary']->avg_order_value) / 
                                     $compareData['summary']->avg_order_value) * 100;
                        @endphp
                        {{ $change >= 0 ? '↑' : '↓' }} {{ abs(round($change, 2)) }}%
                    </small>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Biểu đồ doanh thu theo ngày</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Top sản phẩm bán chạy</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>SKU</th>
                            <th>Số lượng</th>
                            <th>Doanh thu</th>
                            <th>Doanh thu thuần</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topProducts as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                       <img src="{{ asset('storage/' .$product->image ?? '/images/default-product.jpg') }}" 
                                             width="40" height="40" class="rounded mr-2">
                                       
                                        {{ $product->product_name }}
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->total_quantity }}</td>
                                <td>{{ number_format($product->total_revenue) }}đ</td>
                                <td>{{ number_format($product->gross_profit) }}đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Doanh thu theo danh mục</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Danh mục</th>
                            <th>Số lượng bán</th>
                            <th>Doanh thu</th>
                            <th>Doanh thu thuần</th>
                            <th>Tỷ trọng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalRevenue = $revenueByCategory->sum('total_revenue') @endphp
                        @foreach ($revenueByCategory as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->total_quantity }}</td>
                                <td>{{ number_format($category->total_revenue) }}đ</td>
                                <td>{{ number_format($category->gross_profit) }}đ</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0 }}%" 
                                             aria-valuenow="{{ $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0 }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ round($totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Trạng thái đơn hàng</h5>
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
                                @php $totalOrders = $orderStatusStats->sum('count') @endphp
                                @foreach ($orderStatusStats as $status)
                                    <tr>
                                        <td>{{ $status->status }}</td>
                                        <td>{{ $status->count }}</td>
                                        <td>
                                            @if ($totalOrders > 0)
                                                {{ round(($status->count / $totalOrders) * 100, 2) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

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

        // Revenue Chart - Phiên bản đơn giản hơn
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const revenueData = {
        labels: @json($revenueByDate->pluck('date')->toArray()),
        datasets: [{
            label: 'Doanh thu',
            data: @json($revenueByDate->pluck('total_revenue')->toArray()),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };
    
    console.log('Revenue Chart Data:', revenueData); // Debug
    
    new Chart(revenueCtx, {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
        // Order Status Chart
        const statusCtx = document.getElementById('orderStatusChart');
        if (statusCtx) {
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($orderStatusStats->pluck('status')),
                    datasets: [{
                        data: @json($orderStatusStats->pluck('count')),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush