@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
    
    <div class="row">
        <!-- Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Đơn hàng hôm nay</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($orderStats['today']['total']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Doanh thu hôm nay</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($orderStats['today']['revenue']) }} VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng sản phẩm</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productStats['total']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Người dùng mới hôm nay</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($userStats['today']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biểu đồ doanh thu -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu 30 ngày gần nhất</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ trạng thái đơn hàng -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng thái đơn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="orderStatusChart" height="300"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($orderStats['status_counts'] as $status => $count)
                        <span class="mr-2 d-inline-block mb-1">
                            <i class="fas fa-circle" style="color: {{ $statusColors[$status] ?? '#858796' }}"></i> 
                            {{ $statusNames[$status] ?? $status }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng đơn hàng mới nhất và sản phẩm tồn kho thấp -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng mới nhất</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0">Mã đơn</th>
                                    <th class="border-top-0">Khách hàng</th>
                                    <th class="border-top-0 text-right">Tổng tiền</th>
                                    <th class="border-top-0">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->user->name ?? 'Khách vãng lai' }}</td>
                                    <td class="text-right">{{ number_format($order->total) }} VNĐ</td>
                                    <td>
                                        <span class="badge badge-{{ $order->status_color }}">
                                            {{ $order->status_name }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Không có đơn hàng nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sản phẩm tồn kho thấp</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0">Tên sản phẩm</th>
                                    <th class="border-top-0">SKU</th>
                                    <th class="border-top-0 text-right">Tồn kho</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $product)
                                    @foreach($product->variants as $variant)
                                    <tr>
                                        <td>
                                            {{ $product->name }}
                                            @if($variant->color)
                                                - {{ $variant->color->name }}
                                            @endif
                                            @if($variant->size)
                                                - {{ $variant->size->name }}
                                            @endif
                                        </td>
                                        <td>{{ $variant->sku }}</td>
                                        <td class="text-right {{ $variant->stock->quantity == 0 ? 'text-danger' : 'text-warning' }}">
                                            {{ $variant->stock->quantity }}
                                        </td>
                                    </tr>
                                    @endforeach
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">Không có sản phẩm nào tồn kho thấp</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chart-area, .chart-pie {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    @media (min-width: 768px) {
        .chart-area {
            height: 350px;
        }
    }
    
    .badge-warning { background-color: #f6c23e; color: #fff; }
    .badge-info { background-color: #36b9cc; color: #fff; }
    .badge-success { background-color: #1cc88a; color: #fff; }
    .badge-danger { background-color: #e74a3b; color: #fff; }
    .badge-primary { background-color: #4e73df; color: #fff; }
    .badge-secondary { background-color: #858796; color: #fff; }
    .badge-dark { background-color: #5a5c69; color: #fff; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ doanh thu
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        var revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($revenueStats['daily'])) !!},
                datasets: [{
                    label: "Doanh thu",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: {!! json_encode(array_values($revenueStats['daily'])) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value) {
                                return value.toLocaleString() + ' VNĐ';
                            }
                        },
                        grid: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        titleMarginBottom: 10,
                        titleColor: '#6e707e',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + context.parsed.y.toLocaleString() + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    }

    // Biểu đồ trạng thái đơn hàng
    var statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        var statusLabels = {!! json_encode(array_map(function($status) use ($statusNames) { 
            return $statusNames[$status] ?? $status; 
        }, array_keys($orderStats['status_counts']))) !!};
        
        var statusData = {!! json_encode(array_values($orderStats['status_counts'])) !!};
        var statusColors = {!! json_encode(array_map(function($status) use ($statusColors) { 
            return $statusColors[$status] ?? '#858796'; 
        }, array_keys($orderStats['status_counts']))) !!};
        
        var orderStatusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
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
                        padding: 15,
                        displayColors: false,
                        caretPadding: 10,
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            }
        });
    }
});
</script>
@endpush