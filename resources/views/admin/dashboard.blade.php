@extends('admin.layouts.app')

@section('content')
<div class="container-fluid dashboard-compact">
    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Doanh thu -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Doanh thu hôm nay</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $revenue['formatted']['today'] }}đ</div>
                            <div class="mt-2">
                                <span class="{{ $revenue['daily_change'] >= 0 ? 'text-success' : 'text-danger' }} small">
                                    <i class="fas fa-arrow-{{ $revenue['daily_change'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($revenue['daily_change']) }}% vs hôm qua
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng đơn hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders['total'] }}</div>
                            <div class="mt-2 d-flex justify-content-between">
                                <span class="badge bg-success text-white small">
                                    {{ $orders['status_counts']['completed'] ?? 0 }} hoàn thành
                                </span>
                                <span class="badge bg-warning text-dark small">
                                    {{ $orders['status_counts']['pending'] ?? 0 }} chờ xử lý
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khách hàng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Khách hàng mới (tháng)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $customers['new_this_month'] }}</div>
                            <div class="mt-2 d-flex justify-content-between">
                                <span class="{{ $customers['monthly_change'] >= 0 ? 'text-success' : 'text-danger' }} small">
                                    <i class="fas fa-arrow-{{ $customers['monthly_change'] >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($customers['monthly_change']) }}%
                                </span>
                                <span class="text-primary small">
                                    {{ $customers['return_rate'] }}% quay lại
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tồn kho -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sản phẩm tồn kho</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $products['low_stock'] }} sắp hết</div>
                            <div class="mt-2 d-flex justify-content-between">
                                <span class="text-danger small">
                                    {{ $products['out_of_stock'] }} hết hàng
                                </span>
                                <span class="text-success small">
                                    {{ $products['total'] - $products['out_of_stock'] }} có sẵn
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và Thống kê -->
    <div class="row">
        <!-- Biểu đồ doanh thu -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu 7 ngày gần nhất</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Tùy chọn:</div>
                            <a class="dropdown-item" href="#">Xem chi tiết</a>
                            <a class="dropdown-item" href="#">Xuất báo cáo</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phân bố trạng thái -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng thái đơn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($status_info['names'] as $status => $name)
                            @if(isset($orders['status_counts'][$status]))
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: {{ $status_info['colors'][$status] }}"></i> 
                                {{ $name }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cảnh báo và Top sản phẩm -->
    <div class="row">
        <!-- Cảnh báo khẩn cấp -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Cảnh báo khẩn cấp
                    </h6>
                    <span class="badge bg-white text-danger">{{ $orders['pending_24h'] }}</span>
                </div>
                <div class="card-body">
                    @if($pendingOrders->count() > 0)
                        <div class="alert alert-danger small mb-3">
                            Có <strong>{{ $orders['pending_24h'] }} đơn hàng</strong> chờ xử lý quá 24 giờ
                        </div>
                        <div class="list-group">
                            @foreach($pendingOrders as $order)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Đơn hàng #{{ $order->id }}</h6>
                                    <small>{{ $order->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="d-flex w-100 justify-content-between">
                                    <small>{{ $order->user->name ?? 'Khách vãng lai' }}</small>
                                    <strong class="text-danger">{{ number_format($order->total) }}đ</strong>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="mb-0">Không có đơn hàng chờ xử lý quá 24 giờ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top sản phẩm -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top sản phẩm bán chạy</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-right">Đã bán</th>
                                    <th class="text-right">Tồn kho</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $product->image_url ?? asset('images/default-product.png') }}" 
                                                 class="rounded mr-2" width="40" height="40">
                                            <div>
                                                <h6 class="mb-0">{{ Str::limit($product->name, 25) }}</h6>
                                                <small class="text-muted">{{ $product->category->name ?? 'Không phân loại' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right align-middle">{{ $product->sold }}</td>
                                    <td class="text-right align-middle">
                                        <span class="badge {{ $product->stock < 5 ? 'bg-danger' : ($product->stock < 10 ? 'bg-warning' : 'bg-success') }}">
                                            {{ $product->stock }}
                                        </span>
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
</div>
@endsection

@section('styles')
<style>
.dashboard-compact {
    max-height: 100vh;
    overflow-y: auto;
    padding-bottom: 20px;
}
.chart-area {
    position: relative;
    height: 20rem;
}
.chart-pie {
    position: relative;
    height: 15rem;
}
@media (min-width: 768px) {
    .chart-area {
        height: 20rem;
    }
    .chart-pie {
        height: 15rem;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: @json(array_values($revenue['daily_data']['labels'])),
                datasets: [{
                    label: 'Doanh thu',
                    data: @json(array_values($revenue['daily_data']['data'])),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString() + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value;
                            }
                        }
                    }
                }
            }
        });

// Biểu đồ trạng thái đơn hàng
var ctx2 = document.getElementById("orderStatusChart");
var orderStatusChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($status_info['names'] as $status => $name)
                @if(isset($orders['status_counts'][$status]))
                    "{{ $name }}",
                @endif
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($status_info['names'] as $status => $name)
                    @if(isset($orders['status_counts'][$status]))
                        {{ $orders['status_counts'][$status] }},
                    @endif
                @endforeach
            ],
            backgroundColor: [
                @foreach($status_info['names'] as $status => $name)
                    @if(isset($orders['status_counts'][$status]))
                        "{{ $status_info['colors'][$status] }}",
                    @endif
                @endforeach
            ],
            hoverBackgroundColor: [
                @foreach($status_info['names'] as $status => $name)
                    @if(isset($orders['status_counts'][$status]))
                        "{{ $status_info['colors'][$status] }}",
                    @endif
                @endforeach
            ],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.raw !== null) {
                            label += context.raw + ' đơn (' + Math.round(context.parsed * 100 / context.dataset.data.reduce((a, b) => a + b, 0)) + '%)';
                        }
                        return label;
                    }
                }
            },
            legend: {
                display: false
            },
        },
        cutout: '70%',
    },
});
</script>
@endsection