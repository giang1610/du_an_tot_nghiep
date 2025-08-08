@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Báo cáo doanh thu</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Báo cáo doanh thu</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Form chọn báo cáo -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tùy chọn báo cáo</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.revenue') }}" method="GET" id="report-form">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="time_period">Khoảng thời gian</label>
                                    <select name="time_period" id="time_period" class="form-control">
                                        @foreach($periodOptions as $value => $label)
                                            <option value="{{ $value }}" {{ $time_period == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4" id="custom-date-range" style="display: {{ $time_period == 'custom' ? 'block' : 'none' }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="from_date">Từ ngày</label>
                                        <input type="date" name="from_date" id="from_date" class="form-control" 
                                               value="{{ $fromDate ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="to_date">Đến ngày</label>
                                        <input type="date" name="to_date" id="to_date" class="form-control" 
                                               value="{{ $toDate ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="compare_with">So sánh với</label>
                                    <select name="compare_with" id="compare_with" class="form-control">
                                        <option value="">-- Không so sánh --</option>
                                        @foreach($compareOptions as $value => $label)
                                            <option value="{{ $value }}" {{ $compareWith == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-chart-line"></i> Xem báo cáo
                                </button>
                                @if(!$isEmpty)
                                <button type="submit" name="export" value="1" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Xuất Excel
                                </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!$isEmpty)
    <!-- Tổng quan -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Tổng quan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card bg-primary text-white p-3 rounded">
                                <h6 class="stat-title">Tổng doanh thu</h6>
                                <h3 class="stat-value">{{ number_format($summary->total_revenue) }}₫</h3>
                                @if($compareData)
                                <div class="stat-comparison {{ $summary->total_revenue >= $compareData['summary']->total_revenue ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $summary->total_revenue >= $compareData['summary']->total_revenue ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                    {{ number_format(abs($summary->total_revenue - $compareData['summary']->total_revenue)) }}₫
                                    ({{ $compareData['summary']->total_revenue != 0 ? round(abs($summary->total_revenue - $compareData['summary']->total_revenue) / $compareData['summary']->total_revenue * 100, 2) : '∞' }}%)
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-success text-white p-3 rounded">
                                <h6 class="stat-title">Tổng đơn hàng</h6>
                                <h3 class="stat-value">{{ $summary->total_orders }}</h3>
                                @if($compareData)
                                <div class="stat-comparison {{ $summary->total_orders >= $compareData['summary']->total_orders ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $summary->total_orders >= $compareData['summary']->total_orders ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                    {{ abs($summary->total_orders - $compareData['summary']->total_orders) }}
                                    ({{ $compareData['summary']->total_orders != 0 ? round(abs($summary->total_orders - $compareData['summary']->total_orders) / $compareData['summary']->total_orders * 100, 2) : '∞' }}%)
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-warning text-dark p-3 rounded">
                                <h6 class="stat-title">Giá trị đơn trung bình</h6>
                                <h3 class="stat-value">{{ number_format($summary->avg_order_value) }}₫</h3>
                                @if($compareData)
                                <div class="stat-comparison {{ $summary->avg_order_value >= $compareData['summary']->avg_order_value ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $summary->avg_order_value >= $compareData['summary']->avg_order_value ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                    {{ number_format(abs($summary->avg_order_value - $compareData['summary']->avg_order_value)) }}₫
                                    ({{ $compareData['summary']->avg_order_value != 0 ? round(abs($summary->avg_order_value - $compareData['summary']->avg_order_value) / $compareData['summary']->avg_order_value * 100, 2) : '∞' }}%)
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-danger text-white p-3 rounded">
                                <h6 class="stat-title">Đơn hàng lớn nhất</h6>
                                <h3 class="stat-value">{{ number_format($summary->max_order_value) }}₫</h3>
                                @if($compareData)
                                <div class="stat-comparison {{ $summary->max_order_value >= $compareData['summary']->max_order_value ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $summary->max_order_value >= $compareData['summary']->max_order_value ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                    {{ number_format(abs($summary->max_order_value - $compareData['summary']->max_order_value)) }}₫
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doanh thu theo ngày -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Doanh thu theo ngày</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm bán chạy & Trạng thái đơn hàng -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Sản phẩm bán chạy</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Số lượng</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $product->image ?? asset('images/default-product.png') }}" 
                                                 alt="{{ $product->product_name }}" width="40" class="me-2">
                                            <div>
                                                <div class="fw-bold">{{ $product->product_name }}</div>
                                                <small class="text-muted">{{ $product->sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ $product->total_quantity }}</td>
                                    <td class="text-end">{{ number_format($product->total_revenue) }}₫</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Trạng thái đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="orderStatusChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Trạng thái</th>
                                            <th class="text-end">Số lượng</th>
                                            <th class="text-end">Tổng giá trị</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orderStatusStats as $status)
                                        <tr>
                                            <td>{{ $status->status_name }}</td>
                                            <td class="text-end">{{ $status->count }}</td>
                                            <td class="text-end">{{ number_format($status->total_value) }}₫</td>
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
    </div>

    <!-- Phân tích khách hàng & Tồn kho -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Phân tích khách hàng</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="customerTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="loyalty-tab" data-bs-toggle="tab" data-bs-target="#loyalty" type="button" role="tab">Loyalty</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="frequency-tab" data-bs-toggle="tab" data-bs-target="#frequency" type="button" role="tab">Purchase Frequency</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="region-tab" data-bs-toggle="tab" data-bs-target="#region" type="button" role="tab">Region</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="customerTabContent">
                        <div class="tab-pane fade show active" id="loyalty" role="tabpanel">
                            <canvas id="customerLoyaltyChart" height="200"></canvas>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span>Khách hàng mới</span>
                                    <span class="fw-bold">{{ $customerLoyalty->new_customers }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Khách hàng quay lại</span>
                                    <span class="fw-bold">{{ $customerLoyalty->returning_customers }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Khách hàng thân thiết</span>
                                    <span class="fw-bold">{{ $customerLoyalty->loyal_customers }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="frequency" role="tabpanel">
                            <canvas id="purchaseFrequencyChart" height="250"></canvas>
                        </div>
                        <div class="tab-pane fade" id="region" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Khu vực</th>
                                            <th class="text-end">Số khách</th>
                                            <th class="text-end">Doanh thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customerRegions as $region)
                                        <tr>
                                            <td>{{ $region->region ?? 'Không xác định' }}</td>
                                            <td class="text-end">{{ $region->count }}</td>
                                            <td class="text-end">{{ number_format($region->total_revenue) }}₫</td>
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

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Tồn kho</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card bg-light p-2 rounded text-center">
                                <h6 class="stat-title">Tổng sản phẩm</h6>
                                <h4 class="stat-value">{{ $inventoryStats->total_products }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-light p-2 rounded text-center">
                                <h6 class="stat-title">Giá trị tồn kho</h6>
                                <h4 class="stat-value">{{ number_format($inventoryStats->total_inventory_value) }}₫</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card bg-light p-2 rounded text-center">
                                <h6 class="stat-title">Hết hàng</h6>
                                <h4 class="stat-value">{{ $inventoryStats->out_of_stock_products }}</h4>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Sản phẩm tồn kho thấp</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Tồn kho</th>
                                    <th class="text-end">Giá trị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventoryProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-end {{ $product->stock_quantity <= 0 ? 'text-danger fw-bold' : '' }}">
                                        {{ $product->stock_quantity }}
                                    </td>
                                    <td class="text-end">{{ number_format($product->inventory_value) }}₫</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Không có dữ liệu báo cáo trong khoảng thời gian đã chọn.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Hiển thị/ẩn custom date range
    document.getElementById('time_period').addEventListener('change', function() {
        const customDateRange = document.getElementById('custom-date-range');
        customDateRange.style.display = this.value === 'custom' ? 'block' : 'none';
    });

    // Revenue Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = @json($revenueByDate);
        const compareData = @json($compareData ? $compareData['revenueByDate'] : []);
        
        const labels = revenueData.map(item => item.date);
        const revenueValues = revenueData.map(item => item.total_revenue);
        
        let compareValues = [];
        if (compareData.length > 0) {
            compareValues = labels.map(date => {
                const found = compareData.find(item => item.date === date);
                return found ? found.total_revenue : 0;
            });
        }
        
        const datasets = [{
            label: 'Doanh thu',
            data: revenueValues,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.1
        }];
        
        if (compareValues.length > 0) {
            datasets.push({
                label: 'So sánh',
                data: compareValues,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                tension: 0.1,
                borderDash: [5, 5]
            });
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString() + '₫';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + '₫';
                            }
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const statusData = @json($orderStatusStats);
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status_name),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#6c757d'
                    ],
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Customer Loyalty Chart
        const loyaltyCtx = document.getElementById('customerLoyaltyChart').getContext('2d');
        new Chart(loyaltyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Khách mới', 'Khách quay lại', 'Khách thân thiết'],
                datasets: [{
                    data: [
                        {{ $customerLoyalty->new_customers }},
                        {{ $customerLoyalty->returning_customers }},
                        {{ $customerLoyalty->loyal_customers }}
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Purchase Frequency Chart
        const frequencyCtx = document.getElementById('purchaseFrequencyChart').getContext('2d');
        const frequencyData = @json($purchaseFrequency);
        
        new Chart(frequencyCtx, {
            type: 'bar',
            data: {
                labels: frequencyData.map(item => item.frequency_range),
                datasets: [{
                    label: 'Số khách hàng',
                    data: frequencyData.map(item => item.count),
                    backgroundColor: 'rgba(78, 115, 223, 0.5)',
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endpush