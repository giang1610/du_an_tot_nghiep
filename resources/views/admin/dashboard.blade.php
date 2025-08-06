@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-0 px-md-3">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Tổng Quan</h1>
            <div class="d-flex">
                {{-- <button class="btn btn-sm btn-outline-success me-2">
                    <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                </button> --}}
                <a href="{{ route('dashboard.exportExcel') }}" class="btn btn-success mb-3">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
                {{-- <button class="btn btn-sm btn-outline-danger me-2">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Xuất PDF
                </button> --}}
            </div>
        </div>

        <!-- Doanh thu section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card metric-card metric-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="bi bi-currency-dollar me-2"></i>Doanh Thu
                        </h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">Tháng này</div>
                                            <div>{{ number_format($revenueStats['this_month']) }} VNĐ</div>
                                        </div>
                                        <div class="text-end">
                                            @if($revenueStats['monthly_percent_change'] >= 0)
                                                <span class="badge bg-success"><i class="bi bi-arrow-up"></i>
                                                    +{{ abs($revenueStats['monthly_percent_change']) }}%</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-arrow-down"></i>
                                                    {{ abs($revenueStats['monthly_percent_change']) }}%</span>
                                            @endif
                                            <div class="small text-muted">So với tháng trước
                                                ({{ number_format($revenueStats['last_month']) }} VNĐ)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">Tuần này</div>
                                            <div>{{ number_format($revenueStats['this_week']) }} VNĐ</div>
                                        </div>
                                        <div class="text-end">
                                            @if($revenueStats['weekly_percent_change'] >= 0)
                                                <span class="badge bg-success"><i class="bi bi-arrow-up"></i>
                                                    +{{ abs($revenueStats['weekly_percent_change']) }}%</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-arrow-down"></i>
                                                    {{ abs($revenueStats['weekly_percent_change']) }}%</span>
                                            @endif
                                            <div class="small text-muted">So với tuần trước
                                                ({{ number_format($revenueStats['last_week']) }} VNĐ)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">Năm nay</div>
                                            <div>{{ number_format($revenueStats['this_year']) }} VNĐ</div>
                                        </div>
                                        <div class="text-end">
                                            @if($revenueStats['yearly_percent_change'] >= 0)
                                                <span class="badge bg-success"><i class="bi bi-arrow-up"></i>
                                                    +{{ abs($revenueStats['yearly_percent_change']) }}%</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-arrow-down"></i>
                                                    {{ abs($revenueStats['yearly_percent_change']) }}%</span>
                                            @endif
                                            <div class="small text-muted">So với năm trước
                                                ({{ number_format($revenueStats['last_year']) }} VNĐ)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="d-flex flex-column">
                                    <span class="text-muted small">Hôm nay</span>
                                    <div class="d-flex align-items-end">
                                        <h4 class="mb-0 me-2">{{ number_format($orderStats['today']['revenue']) }} VNĐ</h4>
                                        @if($revenueStats['today_percent_change'] >= 0)
                                            <span class="text-success small"><i class="bi bi-arrow-up"></i>
                                                {{ abs($revenueStats['today_percent_change']) }}%</span>
                                        @else
                                            <span class="text-danger small"><i class="bi bi-arrow-down"></i>
                                                {{ abs($revenueStats['today_percent_change']) }}%</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="d-flex flex-column">
                                    <span class="text-muted small">Tuần này</span>
                                    <div class="d-flex align-items-end">
                                        <h4 class="mb-0 me-2">{{ number_format($revenueStats['this_week']) }} VNĐ</h4>
                                        @if($revenueStats['weekly_percent_change'] >= 0)
                                            <span class="text-success small"><i class="bi bi-arrow-up"></i>
                                                {{ abs($revenueStats['weekly_percent_change']) }}%</span>
                                        @else
                                            <span class="text-danger small"><i class="bi bi-arrow-down"></i>
                                                {{ abs($revenueStats['weekly_percent_change']) }}%</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="d-flex flex-column">
                                    <span class="text-muted small">Tháng này</span>
                                    <div class="d-flex align-items-end">
                                        <h4 class="mb-0 me-2">{{ number_format($revenueStats['this_month']) }} VNĐ</h4>
                                        @if($revenueStats['monthly_percent_change'] >= 0)
                                            <span class="text-success small"><i class="bi bi-arrow-up"></i>
                                                {{ abs($revenueStats['monthly_percent_change']) }}%</span>
                                        @else
                                            <span class="text-danger small"><i class="bi bi-arrow-down"></i>
                                                {{ abs($revenueStats['monthly_percent_change']) }}%</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <span class="text-muted small">Năm nay</span>
                                    <div class="d-flex align-items-end">
                                        <h4 class="mb-0 me-2">{{ number_format($revenueStats['this_year']) }} VNĐ</h4>
                                        @if($revenueStats['yearly_percent_change'] >= 0)
                                            <span class="text-success small"><i class="bi bi-arrow-up"></i>
                                                {{ abs($revenueStats['yearly_percent_change']) }}%</span>
                                        @else
                                            <span class="text-danger small"><i class="bi bi-arrow-down"></i>
                                                {{ abs($revenueStats['yearly_percent_change']) }}%</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sparkline-container mt-3">
                            <canvas id="revenueTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics row -->
        <div class="row mb-4">
            <!-- Đơn hàng -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card metric-card metric-success h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-3">
                            <i class="bi bi-cart me-2"></i>Đơn Hàng
                        </h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="mb-0">{{ number_format($orderStats['today']['total']) }}</h2>
                                <small class="text-muted">Hôm nay</small>
                            </div>
                            <div class="text-end">
                                <div class="text-success">{{ $orderStats['completed_percentage'] }}%</div>
                                <small class="text-muted">Hoàn thành</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $orderStats['completed_percentage'] }}%">
                            </div>
                            <div class="progress-bar bg-danger" style="width: {{ $orderStats['cancelled_percentage'] }}%">
                            </div>
                            <div class="progress-bar bg-warning" style="width: {{ $orderStats['pending_percentage'] }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-success">{{ $orderStats['completed_count'] }} đơn</span>
                            <span class="text-danger">{{ $orderStats['cancelled_count'] }} đơn</span>
                            <span class="text-warning">{{ $orderStats['pending_count'] }} đơn</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Đơn chờ xử lý -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card metric-card metric-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning mb-3">
                            <i class="bi bi-clock me-2"></i>Đơn Chờ Xử Lý
                        </h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="mb-0">{{ $orderStats['pending_count'] }}</h2>
                                <small class="text-muted">Tổng số đơn chờ</small>
                            </div>
                            <div class="text-end">
                                <div class="text-danger">{{ $orderStats['overdue_count'] }}</div>
                                <small class="text-muted">>24 giờ</small>
                            </div>
                        </div>
                        @if($orderStats['overdue_count'] > 0)
                            <div class="alert alert-warning p-2 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <small>{{ $orderStats['overdue_count'] }} đơn cần xử lý gấp</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Khách hàng mới -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card metric-card metric-info h-100">
                    <div class="card-body">
                        <h5 class="card-title text-info mb-3">
                            <i class="bi bi-people me-2"></i>Khách Hàng
                        </h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="mb-0">{{ number_format($userStats['today']) }}</h2>
                                <small class="text-muted">Khách mới hôm nay</small>
                            </div>
                            <div class="text-end">
                                @if($userStats['today_percent_change'] >= 0)
                                    <div class="text-success">+{{ $userStats['today_percent_change'] }}%</div>
                                @else
                                    <div class="text-danger">{{ $userStats['today_percent_change'] }}%</div>
                                @endif
                                <small class="text-muted">So hôm qua</small>
                            </div>
                        </div>
                        <div class="sparkline-container">
                            <canvas id="customerTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tỉ lệ quay lại -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card metric-card metric-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="bi bi-arrow-repeat me-2"></i>Tỉ Lệ Quay Lại
                        </h5>
                        <div class="d-flex justify-content-center mb-3">
                            <div style="width: 120px; height: 120px;">
                                <canvas id="retentionRate"></canvas>
                            </div>
                        </div>
                        <div class="text-center">
                            <h4 class="mb-0">{{ $userStats['retention_rate'] }}%</h4>
                            <small class="text-muted">Khách hàng quay lại</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts row -->
        <div class="row mb-4">
            <!-- Biểu đồ doanh thu -->
            <div class="col-lg-8 mb-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-primary mb-0" id="revenueTitle">
                                <i class="bi bi-graph-up me-2"></i>Xu Hướng Doanh Thu 30 Ngày
                            </h5>
                            <select class="form-select form-select-sm" style="width: 150px;" id="revenuePeriod">
                                <option value="30" selected>30 ngày</option>
                                <option value="7">7 ngày</option>
                                <option value="90">3 tháng</option>
                            </select>
                        </div>
                        <div style="height: 300px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cảnh báo khẩn cấp -->
            <div class="col-lg-4 mb-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>Cảnh Báo Khẩn
                        </h5>

                        @if($orderStats['cancellation_increase'] > 10)
                            <div class="alert-panel alert-critical mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                                    <div>
                                        <h6 class="mb-0">Tỉ lệ hủy đơn</h6>
                                        <small>Tăng {{ $orderStats['cancellation_increase'] }}% so với tuần trước</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($orderStats['overdue_count'] > 0)
                            <div class="alert-panel bg-light-warning mb-3" style="border-left-color: var(--warning-color);">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock-fill me-2 fs-5 text-warning"></i>
                                    <div>
                                        <h6 class="mb-0">Đơn chậm xử lý</h6>
                                        <small>{{ $orderStats['overdue_count'] }} đơn >24 giờ</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(count($lowStockProducts) > 0)
                            <div class="alert-panel bg-light-info" style="border-left-color: var(--info-color);">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2 fs-5 text-info"></i>
                                    <div>
                                        <h6 class="mb-0">Sản phẩm tồn kho</h6>
                                        <small>{{ count($lowStockProducts) }} sản phẩm sắp hết hàng</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top sản phẩm -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="bi bi-trophy me-2"></i>Top Sản Phẩm Bán Chạy
                        </h5>
                        <div style="height: 250px;">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            <i class="bi bi-pie-chart me-2"></i>Trạng Thái Đơn Hàng
                        </h5>
                        <div style="height: 250px;">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                           <div class="mt-3 d-flex flex-wrap justify-content-center gap-3">
                                @php
                                    $totalOrders = $orderStats['total_orders'] ?? array_sum($orderStats['status_counts']);
                                @endphp
                                @foreach($orderStats['status_counts'] as $status => $count)
                                    @php
                                        $percent = $totalOrders > 0 ? round($count / $totalOrders * 100, 1) : 0;
                                    @endphp
                                    <span class="d-flex align-items-center gap-1" style="min-width: 140px;">
                                        <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $statusColors[$status] ?? '#858796' }};"></span>
                                        <span style="font-size: 14px;">
                                            {{ $statusNames[$status] ?? ucfirst($status) }}
                                            <span class="text-muted" style="font-size: 13px;">({{ $percent }}%)</span>
                                        </span>
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
                <div class="card metric-card h-100">
                    <div class="card-header">
                        <h5 class="card-title text-primary mb-0">
                            <i class="bi bi-cart me-2"></i>Đơn Hàng Mới Nhất
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th class="text-right">Tổng tiền</th>
                                        <th>Trạng thái</th>
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
                <div class="card metric-card h-100">
                    <div class="card-header">
                        <h5 class="card-title text-primary mb-0">
                            <i class="bi bi-box-seam me-2"></i>Sản Phẩm Tồn Kho Thấp
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tên sản phẩm</th>
                                        <th>SKU</th>
                                        <th class="text-right">Tồn kho</th>
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
                                                <td
                                                    class="text-right {{ $variant->stock->quantity == 0 ? 'text-danger' : 'text-warning' }}">
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
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }

        .metric-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            height: 100%;
            border: none;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .metric-card .card-body {
            padding: 1.25rem;
        }

        /* Color coding */
        .metric-primary {
            border-left: 4px solid var(--primary-color);
        }

        .metric-success {
            border-left: 4px solid var(--success-color);
        }

        .metric-info {
            border-left: 4px solid var(--info-color);
        }

        .metric-warning {
            border-left: 4px solid var(--warning-color);
        }

        /* Sparkline charts */
        .sparkline-container {
            height: 40px;
            margin-top: 10px;
        }

        /* Alert panel */
        .alert-panel {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid;
        }

        .alert-critical {
            animation: pulse 2s infinite;
            border-left: 4px solid var(--danger-color);
        }

        .bg-light-warning {
            background-color: rgba(246, 194, 62, 0.1);
        }

        .bg-light-info {
            background-color: rgba(54, 185, 204, 0.1);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(231, 74, 59, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            }
        }

        .badge-warning {
            background-color: var(--warning-color);
            color: #fff;
        }

        .badge-info {
            background-color: var(--info-color);
            color: #fff;
        }

        .badge-success {
            background-color: var(--success-color);
            color: #fff;
        }

        .badge-danger {
            background-color: var(--danger-color);
            color: #fff;
        }

        .badge-primary {
            background-color: var(--primary-color);
            color: #fff;
        }

    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue Trend Sparkline
            new Chart(
                document.getElementById('revenueTrend'),
                {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(array_keys($revenueStats['daily_30'])) !!},
                        datasets: [{
                            data: {!! json_encode(array_values($revenueStats['daily_30'])) !!},
                            borderColor: '#4e73df',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { display: false } },
                        maintainAspectRatio: false
                    }
                }
            );

            // Customer Trend Sparkline
            new Chart(
                document.getElementById('customerTrend'),
                {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($userStats['last_7_days_labels']) !!},
                        datasets: [{
                            data: {!! json_encode($userStats['last_7_days_data']) !!},
                            borderColor: '#36b9cc',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { display: false } },
                        maintainAspectRatio: false
                    }
                }
            );

            // Retention Rate Chart
            new Chart(
                document.getElementById('retentionRate'),
                {
                    type: 'doughnut',
                    data: {
                        labels: ['Quay lại', 'Mới'],
                        datasets: [{
                            data: [{{ $userStats['retention_rate'] }}, {{ 100 - $userStats['retention_rate'] }}],
                            backgroundColor: ['#4e73df', '#e3e6f0'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: { legend: { display: false } },
                        maintainAspectRatio: false
                    }
                }
            );

            // Revenue Chart
            // new Chart(
            //     document.getElementById('revenueChart'),
            //     {
            //         type: 'line',
            //         data: {
            //             labels: {!! json_encode(array_keys($revenueStats['daily_30'])) !!},
            //             datasets: [{
            //                 label: "Doanh thu",
            //                 lineTension: 0.3,
            //                 backgroundColor: "rgba(78, 115, 223, 0.05)",
            //                 borderColor: "rgba(78, 115, 223, 1)",
            //                 pointRadius: 3,
            //                 pointBackgroundColor: "rgba(78, 115, 223, 1)",
            //                 pointBorderColor: "rgba(78, 115, 223, 1)",
            //                 pointHoverRadius: 3,
            //                 pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
            //                 pointHoverBorderColor: "rgba(78, 115, 223, 1)",
            //                 pointHitRadius: 10,
            //                 pointBorderWidth: 2,
            //                 data: {!! json_encode(array_values($revenueStats['daily_30'])) !!},
            //             }],
            //         },
            //         options: {
            //             maintainAspectRatio: false,
            //             scales: {
            //                 y: {
            //                     beginAtZero: true,
            //                     ticks: {
            //                         callback: function (value) {
            //                             return value.toLocaleString() + ' VNĐ';
            //                         }
            //                     }
            //                 }
            //             },
            //             plugins: {
            //                 legend: {
            //                     display: false
            //                 },
            //                 tooltip: {
            //                     callbacks: {
            //                         label: function (context) {
            //                             return 'Doanh thu: ' + context.parsed.y.toLocaleString() + ' VNĐ';
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // );

            // Top Products Chart
            new Chart(
                document.getElementById('topProductsChart'),
                {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($topProducts->map(function ($variant) {
        $label = $variant->product->name ?? '';
        if ($variant->color)
            $label .= ' - ' . $variant->color->name;
        if ($variant->size)
            $label .= ' - ' . $variant->size->name;
        return $label;
    })->toArray()) !!},
                        datasets: [{
                            label: 'Số lượng bán',
                            data: {!! json_encode($topProducts->pluck('sold_count')->toArray()) !!},
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#858796']
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true }
                        }
                    }
                }
            );

            // Order Status Chart
            var statusLabels = {!! json_encode(array_map(function ($status) use ($statusNames) {
        return $statusNames[$status] ?? $status;
    }, array_keys($orderStats['status_counts']))) !!};

            var statusData = {!! json_encode(array_values($orderStats['status_counts'])) !!};
            var statusColors = {!! json_encode(array_map(function ($status) use ($statusColors) {
        return $statusColors[$status] ?? '#858796';
    }, array_keys($orderStats['status_counts']))) !!};
            new Chart(
                document.getElementById('orderStatusChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: statusColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: { display: false }
                        },
                        maintainAspectRatio: false
                    }
                }
            );

            var revenueData = {
                30: {
                    labels: {!! json_encode(array_keys($revenueStats['daily_30'])) !!},
                    data: {!! json_encode(array_values($revenueStats['daily_30'])) !!}
                },
                7: {
                    labels: {!! json_encode(array_keys($revenueStats['daily_7'])) !!},
                    data: {!! json_encode(array_values($revenueStats['daily_7'])) !!}
                },
                90: {
                    labels: {!! json_encode(array_keys($revenueStats['daily_90'])) !!},
                    data: {!! json_encode(array_values($revenueStats['daily_90'])) !!}
                }
            };

            var revenueChart = new Chart(
                document.getElementById('revenueChart'),
                {
                    type: 'line',
                    data: {
                        labels: revenueData[30].labels,
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
                            data: revenueData[30].data,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value.toLocaleString() + ' VNĐ';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return 'Doanh thu: ' + context.parsed.y.toLocaleString() + ' VNĐ';
                                    }
                                }
                            }
                        }
                    }
                }
            );

            // Revenue period selector
            document.getElementById('revenuePeriod').addEventListener('change', function () {
                var period = this.value;
                revenueChart.data.labels = revenueData[period].labels;
                revenueChart.data.datasets[0].data = revenueData[period].data;
                revenueChart.update();

                // Đổi tiêu đề
                var title = "Xu Hướng Doanh Thu ";
                if (period == 7) title += "7 Ngày";
                else if (period == 30) title += "30 Ngày";
                else if (period == 90) title += "3 Tháng";
                document.getElementById('revenueTitle').innerHTML = '<i class="bi bi-graph-up me-2"></i>' + title;
            });
        });
    </script>
@endpush
