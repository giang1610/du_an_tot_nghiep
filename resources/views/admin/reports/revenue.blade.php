@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Báo cáo doanh thu chi tiết</h3>
                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse"
                            data-bs-target="#filterCard">
                            <i class="bi bi-funnel"></i> Lọc
                        </button>
                    </div>
                    <div class="card-body collapse show" id="filterCard">
                        <form action="{{ route('admin.reports.revenue') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label"><i class="bi bi-calendar-range me-1"></i> Khoảng thời
                                        gian</label>
                                    <select name="time_period" class="form-select select2">
                                        <option value="today" {{ request('time_period') == 'today' ? 'selected' : '' }}>Hôm
                                            nay</option>
                                        <option value="yesterday"
                                            {{ request('time_period') == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                                        <option value="this_week"
                                            {{ request('time_period') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                                        <option value="last_week"
                                            {{ request('time_period') == 'last_week' ? 'selected' : '' }}>Tuần trước
                                        </option>
                                        <option value="this_month"
                                            {{ request('time_period') == 'this_month' ? 'selected' : '' }}>Tháng này
                                        </option>
                                        <option value="last_month"
                                            {{ request('time_period') == 'last_month' ? 'selected' : '' }}>Tháng trước
                                        </option>
                                        <option value="this_year"
                                            {{ request('time_period') == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                                        <option value="last_year"
                                            {{ request('time_period') == 'last_year' ? 'selected' : '' }}>Năm trước</option>
                                        <option value="custom" {{ request('time_period') == 'custom' ? 'selected' : '' }}>
                                            Tùy chỉnh</option>
                                    </select>
                                </div>

                                <div class="col-md-2" id="from_date_group"
                                    style="{{ request('time_period') == 'custom' ? '' : 'display:none' }}">
                                    <label class="form-label"><i class="bi bi-calendar-minus me-1"></i> Từ ngày</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ request('from_date', date('Y-m-d', strtotime('-30 days'))) }}">
                                </div>

                                <div class="col-md-2" id="to_date_group"
                                    style="{{ request('time_period') == 'custom' ? '' : 'display:none' }}">
                                    <label class="form-label"><i class="bi bi-calendar-plus me-1"></i> Đến ngày</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ request('to_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><i class="bi bi-compass me-1"></i> So sánh với</label>
                                    <select name="compare_with" class="form-select select2">
                                        <option value="">-- Không so sánh --</option>
                                        <option value="previous_period"
                                            {{ request('compare_with') == 'previous_period' ? 'selected' : '' }}>Kỳ trước
                                        </option>
                                        <option value="same_period_last_week"
                                            {{ request('compare_with') == 'same_period_last_week' ? 'selected' : '' }}>Cùng
                                            kỳ tuần trước</option>
                                        <option value="same_period_last_month"
                                            {{ request('compare_with') == 'same_period_last_month' ? 'selected' : '' }}>
                                            Cùng kỳ tháng trước</option>
                                        <option value="same_period_last_year"
                                            {{ request('compare_with') == 'same_period_last_year' ? 'selected' : '' }}>Cùng
                                            kỳ năm trước</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="bi bi-funnel me-1"></i> Lọc
                                        </button>
                                        <button type="submit" name="export" value="1" class="btn btn-success">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Thông tin khoảng thời gian -->
        <div class="alert alert-info">
            <div class="d-flex">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div class="flex-fill">
                    <h5 class="alert-heading mb-2">Thông tin báo cáo</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Khoảng thời gian:</strong> <span id="report-date-range">{{ $fromDate }} -
                                {{ $toDate }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Tổng số ngày:</strong> <span id="report-days-count">{{ $daysCount }}</span> ngày
                        </div>
                        <div class="col-md-4">
                            <strong>Ngày tạo báo cáo:</strong> <span
                                id="report-generation-date">{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($isEmpty)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i> Không có dữ liệu trong khoảng thời gian đã chọn.
            </div>
        @else
            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Thống kê tổng quan</h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-bs-toggle="collapse"
                                    data-bs-target="#overviewStats">
                                    <i class="bi bi-dash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body collapse show" id="overviewStats">
                            <div class="row g-3">
                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-info">
                                            <i class="bi bi-cart"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng đơn hàng</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_orders) }}
                                                @if ($compareData)
                                                    <small
                                                        class="text-{{ $summary->total_orders > $compareData->summary->total_orders ? 'success' : 'danger' }}">
                                                        {{ $summary->total_orders > $compareData->summary->total_orders ? '+' : '' }}{{ number_format($summary->total_orders - $compareData->summary->total_orders) }}
                                                        ({{ $compareData->summary->total_orders > 0 ? number_format((($summary->total_orders - $compareData->summary->total_orders) / $compareData->summary->total_orders) * 100, 1) : '100' }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-success">
                                            <i class="bi bi-currency-dollar"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng doanh thu</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_revenue) }} VNĐ
                                                @if ($compareData)
                                                    <small
                                                        class="text-{{ $summary->total_revenue > $compareData->summary->total_revenue ? 'success' : 'danger' }}">
                                                        {{ $summary->total_revenue > $compareData->summary->total_revenue ? '+' : '' }}{{ number_format($summary->total_revenue - $compareData->summary->total_revenue) }}
                                                        VNĐ
                                                        ({{ $compareData->summary->total_revenue > 0 ? number_format((($summary->total_revenue - $compareData->summary->total_revenue) / $compareData->summary->total_revenue) * 100, 1) : '100' }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-warning">
                                            <i class="bi bi-graph-up"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Giá trị đơn TB</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->avg_order_value) }} VNĐ
                                                @if ($compareData)
                                                    <small
                                                        class="text-{{ $summary->avg_order_value > $compareData->summary->avg_order_value ? 'success' : 'danger' }}">
                                                        {{ $summary->avg_order_value > $compareData->summary->avg_order_value ? '+' : '' }}{{ number_format($summary->avg_order_value - $compareData->summary->avg_order_value) }}
                                                        VNĐ
                                                        ({{ $compareData->summary->avg_order_value > 0 ? number_format((($summary->avg_order_value - $compareData->summary->avg_order_value) / $compareData->summary->avg_order_value) * 100, 1) : '100' }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-danger">
                                            <i class="bi bi-tag"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng giảm giá</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_discount) }} VNĐ
                                                @if ($compareData)
                                                    <small
                                                        class="text-{{ $summary->total_discount > $compareData->summary->total_discount ? 'success' : 'danger' }}">
                                                        {{ $summary->total_discount > $compareData->summary->total_discount ? '+' : '' }}{{ number_format($summary->total_discount - $compareData->summary->total_discount) }}
                                                        VNĐ
                                                        ({{ $compareData->summary->total_discount > 0 ? number_format((($summary->total_discount - $compareData->summary->total_discount) / $compareData->summary->total_discount) * 100, 1) : '100' }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm bg-light">
                                        <span class="info-box-icon bg-primary">
                                            <i class="bi bi-receipt"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Doanh thu trước thuế</span>
                                            <span class="info-box-number">{{ number_format($summary->subtotal) }}
                                                VNĐ</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm bg-light">
                                        <span class="info-box-icon bg-secondary">
                                            <i class="bi bi-file-text"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng thuế</span>
                                            <span class="info-box-number">{{ number_format($summary->total_tax) }}
                                                VNĐ</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm bg-light">
                                        <span class="info-box-icon bg-info">
                                            <i class="bi bi-truck"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Phí vận chuyển</span>
                                            <span class="info-box-number">{{ number_format($summary->total_shipping) }}
                                                VNĐ</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="info-box shadow-sm bg-light">
                                        <span class="info-box-icon bg-purple">
                                            <i class="bi bi-calendar-check"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Đơn hàng/ngày</span>
                                            <span class="info-box-number">
                                                {{ $daysCount > 0 ? number_format($summary->total_orders / $daysCount, 1) : 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ doanh thu -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-bar-chart me-2"></i>Biểu đồ doanh thu theo ngày
                            </h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-bs-toggle="collapse"
                                    data-bs-target="#revenueChartCard">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <button type="button" class="btn btn-tool fullscreen-btn">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body collapse show" id="revenueChartCard">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Các tab thông tin chi tiết -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="payment-methods-tab" data-bs-toggle="tab"
                                        data-bs-target="#payment-methods" type="button" role="tab"
                                        aria-controls="payment-methods" aria-selected="true">
                                        <i class="bi bi-credit-card me-1"></i> Thanh toán
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="categories-tab" data-bs-toggle="tab"
                                        data-bs-target="#categories" type="button" role="tab"
                                        aria-controls="categories" aria-selected="false">
                                        <i class="bi bi-tags me-1"></i> Danh mục
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id=" s-tab" data-bs-toggle="tab"
                                        data-bs-target="#products" type="button" role="tab"
                                        aria-controls="products" aria-selected="false">
                                        <i class="bi bi-boxes me-1"></i> Sản phẩm
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="statuses-tab" data-bs-toggle="tab"
                                        data-bs-target="#statuses" type="button" role="tab"
                                        aria-controls="statuses" aria-selected="false">
                                        <i class="bi bi-clipboard-check me-1"></i> Trạng thái
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="customers-tab" data-bs-toggle="tab"
                                        data-bs-target="#customers" type="button" role="tab"
                                        aria-controls="customers" aria-selected="false">
                                        <i class="bi bi-people me-1"></i> Khách hàng
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="vouchers-tab" data-bs-toggle="tab"
                                        data-bs-target="#vouchers" type="button" role="tab"
                                        aria-controls="vouchers" aria-selected="false">
                                        <i class="bi bi-ticket-perforated me-1"></i> Voucher
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab"
                                        data-bs-target="#inventory" type="button" role="tab"
                                        aria-controls="inventory" aria-selected="false">
                                        <i class="bi bi-house-door me-1"></i> Tồn kho
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="reportTabsContent">
                                <!-- Phương thức thanh toán -->
<div class="tab-pane fade show active" id="payment-methods" role="tabpanel" aria-labelledby="payment-methods-tab">
    <div class="row">
        <div class="col-lg-5">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">Phương thức</th>
                            <th class="text-center">Số đơn</th>
                            <th class="text-center">Doanh thu</th>
                            <th class="text-center">Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalOrders = $revenueByPaymentMethod->sum('order_count');
                            $totalRevenue = $revenueByPaymentMethod->sum('total_revenue');
                        @endphp
                        @foreach($revenueByPaymentMethod as $payment)
                        @php
                            $percentage = $totalOrders > 0 ? ($payment->order_count / $totalOrders) * 100 : 0;
                            // Chuẩn hóa tên phương thức thanh toán
                            $paymentName = match(strtolower($payment->payment_method)) {
                                'cod' => 'COD',
                                'momo' => 'Momo',
                                'vnpay' => 'VNPay',
                                'bank_transfer' => 'Chuyển khoản',
                                'paypal' => 'PayPal',
                                'credit_card' => 'Thẻ tín dụng',
                                default => $payment->payment_method
                            };
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="badge" style="background-color: {{ $payment->color }}; color: white;">
                                    {{ $paymentName }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($payment->order_count) }}</td>
                            <td class="text-end">{{ number_format($payment->total_revenue) }} VNĐ</td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ $percentage }}%; background-color: {{ $payment->color }};"
                                         aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="table-light">
                            <th class="text-center">Tổng cộng</th>
                            <th class="text-end">{{ number_format($totalOrders) }}</th>
                            <th class="text-end">{{ number_format($totalRevenue) }} VNĐ</th>
                            <th>100%</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="chart-container">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>
    </div>
</div>

                                <!-- Danh mục sản phẩm -->
                                <div class="tab-pane fade" id="categories" role="tabpanel"
                                    aria-labelledby="categories-tab">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-success">
                                                        <tr>
                                                            <th>Danh mục</th>
                                                            <th class="text-center">SL bán</th>
                                                            <th class="text-center">Doanh thu</th>
                                                            <th class="text-center">Tỷ lệ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $totalCategoryRevenue = $revenueByCategory->sum(
                                                                'total_revenue',
                                                            );
                                                        @endphp
                                                        @foreach ($revenueByCategory as $category)
                                                            @php
                                                                $percentage =
                                                                    $totalCategoryRevenue > 0
                                                                        ? ($category->total_revenue /
                                                                                $totalCategoryRevenue) *
                                                                            100
                                                                        : 0;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $category->name }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($category->total_quantity) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($category->total_revenue) }} VNĐ</td>
                                                                <td>
                                                                    <div class="progress" style="height: 8px;">
                                                                        <div class="progress-bar bg-success"
                                                                            role="progressbar"
                                                                            style="width: {{ $percentage }}%"
                                                                            aria-valuenow="{{ $percentage }}"
                                                                            aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                    <small
                                                                        class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="table-light">
                                                            <th>Tổng cộng</th>
                                                            <th class="text-end">
                                                                {{ number_format($revenueByCategory->sum('total_quantity')) }}
                                                            </th>
                                                            <th class="text-end">
                                                                {{ number_format($totalCategoryRevenue) }} VNĐ</th>
                                                            <th>100%</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="chart-container">
                                                <canvas id="categoriesChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sản phẩm bán chạy -->
                                <div class="tab-pane fade" id="products" role="tabpanel"
                                    aria-labelledby="products-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-warning">
                                                <tr>
                                                    <th class="text-center" style="width: 50px">STT</th>
                                                    <th>Sản phẩm</th>
                                                    <th class="text-center">SKU</th>
                                                    <th class="text-center">Số lượng</th>
                                                    <th class="text-center">Doanh thu</th>
                                                    <th class="text-center">Tỷ lệ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalProductRevenue = $topProducts->sum('total_revenue');
                                                @endphp
                                                @foreach ($topProducts as $index => $product)
                                                    @php
                                                        $percentage =
                                                            $totalProductRevenue > 0
                                                                ? ($product->total_revenue / $totalProductRevenue) * 100
                                                                : 0;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>{{ $product->product_name }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">{{ $product->sku }}</span>
                                                        </td>
                                                        <td class="text-end">{{ number_format($product->total_quantity) }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($product->total_revenue) }}
                                                            VNĐ</td>
                                                        <td>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-warning" role="progressbar"
                                                                    style="width: {{ $percentage }}%"
                                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                    aria-valuemax="100"></div>
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Trạng thái đơn hàng -->
<div class="tab-pane fade" id="statuses" role="tabpanel" aria-labelledby="statuses-tab">
    <div class="row">
        <div class="col-lg-5">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-danger">
                        <tr>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Số đơn</th>
                            <th class="text-center">Tỷ lệ</th>
                            <th class="text-center">Tổng giá trị</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalStatusOrders = $orderStatusStats->sum('count');
                        @endphp
                        @foreach($orderStatusStats as $status)
                        @php
                            $percentage = $totalStatusOrders > 0 ? ($status->count / $totalStatusOrders) * 100 : 0;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="badge" style="background-color: {{ $status->color }}; color: white;">
                                    {{ $status->status_name }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($status->count) }}</td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ $percentage }}%; background-color: {{ $status->color }};"
                                         aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                            </td>
                            <td class="text-end">{{ number_format($status->total_value) }} VNĐ</td>
                        </tr>
                        @endforeach
                        <tr class="table-light">
                            <th class="text-center">Tổng cộng</th>
                            <th class="text-end">{{ number_format($totalStatusOrders) }}</th>
                            <th>100%</th>
                            <th class="text-end">{{ number_format($orderStatusStats->sum('total_value')) }} VNĐ</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="pt-5 chart-container">
                <canvas id="statusesChart"></canvas>
            </div>
        </div>
        <!-- Thêm sau biểu đồ trạng thái đơn hàng -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-currency-dollar me-2"></i>Doanh thu theo trạng thái</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#revenueByStatusChartCard">
                        <i class="bi bi-dash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="revenueByStatusChartCard">
                <div class="chart-container">
                    <canvas id="revenueByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

                                <!-- Thông tin khách hàng -->
                                <div class="tab-pane fade" id="customers" role="tabpanel"
                                    aria-labelledby="customers-tab">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-info elevation-1">
                                                    <i class="bi bi-person-plus"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách hàng mới</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($customerLoyalty->new_customers) }}
                                                        <small
                                                            class="text-muted">{{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->new_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%</small>
                                                    </span>
                                                    <small class="text-muted">(Mua hàng lần đầu)</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-success elevation-1">
                                                    <i class="bi bi-person-check"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách quay lại</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($customerLoyalty->returning_customers) }}
                                                        <small
                                                            class="text-muted">{{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->returning_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%</small>
                                                    </span>
                                                    <small class="text-muted">(Mua hàng lần 2)</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-warning elevation-1">
                                                    <i class="bi bi-person-heart"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách hàng thân thiết</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($customerLoyalty->loyal_customers) }}
                                                        <small
                                                            class="text-muted">{{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->loyal_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%</small>
                                                    </span>
                                                    <small class="text-muted">(Mua từ 3 lần trở lên)</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-primary elevation-1">
                                                    <i class="bi bi-people"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tổng khách hàng</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($customerLoyalty->total_customers) }}
                                                    </span>
                                                    <small class="text-muted">(Đã mua hàng trong kỳ)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Phân bố khách hàng</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-container">
                                                        <canvas id="customersChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Thống kê khách hàng</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 50%">Tổng số khách hàng mua hàng:</th>
                                                                <td class="text-end">
                                                                    {{ number_format($customerLoyalty->total_customers) }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Khách hàng mới (lần đầu):</th>
                                                                <td class="text-end">
                                                                    {{ number_format($customerLoyalty->new_customers) }}
                                                                    ({{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->new_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%)
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Khách quay lại (lần 2):</th>
                                                                <td class="text-end">
                                                                    {{ number_format($customerLoyalty->returning_customers) }}
                                                                    ({{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->returning_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%)
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Khách thân thiết (3+ lần):</th>
                                                                <td class="text-end">
                                                                    {{ number_format($customerLoyalty->loyal_customers) }}
                                                                    ({{ $customerLoyalty->total_customers > 0 ? number_format(($customerLoyalty->loyal_customers / $customerLoyalty->total_customers) * 100, 1) : 0 }}%)
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Voucher sử dụng -->
                                <div class="tab-pane fade" id="vouchers" role="tabpanel"
                                    aria-labelledby="vouchers-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-purple">
                                                <tr>
                                                    <th class="text-center">Mã voucher</th>
                                                    <th>Tên voucher</th>
                                                    <th class="text-center">Loại</th>
                                                    <th class="text-center">Số lần dùng</th>
                                                    <th class="text-center">Tổng giảm giá</th>
                                                    <th class="text-center">Tỷ lệ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalVoucherUsage = $voucherUsage->sum('usage_count');
                                                    $totalVoucherDiscount = $voucherUsage->sum('total_discount');
                                                @endphp
                                                @foreach ($voucherUsage as $voucher)
                                                    @php
                                                        $percentage =
                                                            $totalVoucherUsage > 0
                                                                ? ($voucher->usage_count / $totalVoucherUsage) * 100
                                                                : 0;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $voucher->code }}</span>
                                                        </td>
                                                        <td>{{ $voucher->name }}</td>
                                                        <td class="text-center">
                                                            <span
                                                                class="badge bg-{{ $voucher->type == 'shipping' ? 'info' : 'success' }}">
                                                                {{ $voucher->type == 'shipping' ? 'Miễn phí vận chuyển' : 'Giảm giá sản phẩm' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">{{ number_format($voucher->usage_count) }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($voucher->total_discount) }}
                                                            VNĐ</td>
                                                        <td>
                                                            <div class="progress" style="height: 8px;">
                                                                <div class="progress-bar bg-purple" role="progressbar"
                                                                    style="width: {{ $percentage }}%"
                                                                    aria-valuenow="{{ $percentage }}"
                                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if ($voucherUsage->count() > 0)
                                                    <tr class="table-light">
                                                        <th colspan="3" class="text-center">Tổng cộng</th>
                                                        <th class="text-end">{{ number_format($totalVoucherUsage) }}</th>
                                                        <th class="text-end">{{ number_format($totalVoucherDiscount) }}
                                                            VNĐ</th>
                                                        <th>100%</th>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center">Không có dữ liệu voucher
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Thông tin tồn kho -->
                                <div class="tab-pane fade" id="inventory" role="tabpanel"
                                    aria-labelledby="inventory-tab">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-info elevation-1">
                                                    <i class="bi bi-boxes"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tổng biến thể</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($inventoryStats->total_variants) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-success elevation-1">
                                                    <i class="bi bi-box-seam"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Biến thể có hàng</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($inventoryStats->in_stock_variants) }}
                                                        <small
                                                            class="text-muted">{{ $inventoryStats->total_variants > 0 ? number_format(($inventoryStats->in_stock_variants / $inventoryStats->total_variants) * 100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-danger elevation-1">
                                                    <i class="bi bi-x-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Biến thể hết hàng</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($inventoryStats->out_of_stock_variants) }}
                                                        <small
                                                            class="text-muted">{{ $inventoryStats->total_variants > 0 ? number_format(($inventoryStats->out_of_stock_variants / $inventoryStats->total_variants) * 100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-warning elevation-1">
                                                    <i class="bi bi-currency-dollar"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Giá trị tồn kho</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($inventoryStats->total_inventory_value) }}
                                                        VNĐ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Tình trạng tồn kho (Theo biến thể)</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-container">
                                                        <canvas id="inventoryChart"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Thống kê tồn kho chi tiết</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 50%">Tổng số sản phẩm:</th>
                                                                <td class="text-end">
                                                                    {{ number_format($inventoryStats->total_products) }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tổng số biến thể:</th>
                                                                <td class="text-end">
                                                                    {{ number_format($inventoryStats->total_variants) }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ biến thể có hàng:</th>
                                                                <td class="text-end">
                                                                    {{ $inventoryStats->total_variants > 0 ? number_format(($inventoryStats->in_stock_variants / $inventoryStats->total_variants) * 100, 1) : 0 }}%
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ biến thể hết hàng:</th>
                                                                <td class="text-end">
                                                                    {{ $inventoryStats->total_variants > 0 ? number_format(($inventoryStats->out_of_stock_variants / $inventoryStats->total_variants) * 100, 1) : 0 }}%
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ sản phẩm có hàng:</th>
                                                                <td class="text-end">
                                                                    {{ $inventoryStats->total_products > 0 ? number_format(($inventoryStats->in_stock_products / $inventoryStats->total_products) * 100, 1) : 0 }}%
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ sản phẩm hết hàng:</th>
                                                                <td class="text-end">
                                                                    {{ $inventoryStats->total_products > 0 ? number_format(($inventoryStats->out_of_stock_products / $inventoryStats->total_products) * 100, 1) : 0 }}%
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Giá trị tồn kho trung bình:</th>
                                                                <td class="text-end">
                                                                    {{ $inventoryStats->total_variants > 0 ? number_format($inventoryStats->total_inventory_value / $inventoryStats->total_variants) : 0 }}
                                                                    VNĐ/biến thể</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Khởi tạo Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Hiển thị/ẩn ngày tùy chọn
            $('select[name="time_period"]').change(function() {
                if ($(this).val() === 'custom') {
                    $('#from_date_group, #to_date_group').slideDown();
                } else {
                    $('#from_date_group, #to_date_group').slideUp();
                }
            });

            // Fullscreen toggle
            $('.fullscreen-btn').click(function() {
                const chartCard = $('#revenueChartCard');
                chartCard.toggleClass('fullscreen');

                if (chartCard.hasClass('fullscreen')) {
                    chartCard.css({
                        'position': 'fixed',
                        'top': '0',
                        'left': '0',
                        'width': '100%',
                        'height': '100%',
                        'z-index': '9999',
                        'background': 'white'
                    });
                    $(this).html('<i class="bi bi-fullscreen-exit"></i>');
                } else {
                    chartCard.removeAttr('style');
                    $(this).html('<i class="bi bi-arrows-fullscreen"></i>');
                }

                // Cập nhật kích thước biểu đồ
                window.dispatchEvent(new Event('resize'));
            });

            @if (!$isEmpty)
                // Biểu đồ doanh thu
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    // Tạo mảng chứa tất cả các ngày trong khoảng thời gian
                    const allDates = [];
                    const currentDate = new Date('{{ $fromDate }}');
                    const endDate = new Date('{{ $toDate }}');

                    while (currentDate <= endDate) {
                        allDates.push(new Date(currentDate).toISOString().split('T')[0]);
                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    // Tạo object để dễ dàng truy cập doanh thu theo ngày
                    const revenueByDateMap = {};
                    @foreach ($revenueByDate as $revenue)
                        revenueByDateMap['{{ $revenue->date }}'] = {{ $revenue->total_revenue }};
                    @endforeach

                    // Tạo mảng dữ liệu với giá trị 0 cho những ngày không có dữ liệu
                    const revenueData = allDates.map(date => revenueByDateMap[date] || 0);

                    // Format lại labels để hiển thị đẹp hơn
                    const formattedLabels = allDates.map(date => {
                        const d = new Date(date);
                        return `${d.getDate()}/${d.getMonth() + 1}`;
                    });

                    new Chart(revenueCtx, {
                        type: 'line',
                        data: {
                            labels: formattedLabels,
                            datasets: [{
                                label: 'Doanh thu',
                                data: revenueData,
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                borderColor: 'rgba(13, 110, 253, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                    padding: 10,
                                    cornerRadius: 4,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Doanh thu: ' + context.raw.toLocaleString() +
                                            ' VNĐ';
                                        },
                                        title: function(context) {
                                            const dateIndex = context[0].dataIndex;
                                            const fullDate = allDates[dateIndex];
                                            const d = new Date(fullDate);
                                            return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString() + ' VNĐ';
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Biểu đồ phương thức thanh toán
const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
if (paymentMethodsCtx) {
    const paymentData = @json($revenueByPaymentMethod);
    const labels = paymentData.map(item => {
        // Chuẩn hóa tên phương thức thanh toán
        switch(item.payment_method.toLowerCase()) {
            case 'cod': return 'COD';
            case 'momo': return 'Momo';
            case 'vnpay': return 'VNPay';
            default: return item.payment_method;
        }
    });
    const data = paymentData.map(item => item.total_revenue);
    const colors = paymentData.map(item => item.color);
    const orderCounts = paymentData.map(item => item.order_count);

    new Chart(paymentMethodsCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            const orderCount = orderCounts[context.dataIndex];
                            return [
                                `${label}: ${value.toLocaleString()} VNĐ (${percentage}%)`,
                                `Số đơn: ${orderCount.toLocaleString()}`
                            ];
                        }
                    }
                }
            }
        }
    });
}

                // Biểu đồ danh mục
                const categoriesCtx = document.getElementById('categoriesChart');
                if (categoriesCtx) {
                    const categoryData = @json($revenueByCategory);
                    const labels = categoryData.map(item => item.name);
                    const data = categoryData.map(item => item.total_revenue);

                    new Chart(categoriesCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Doanh thu theo danh mục',
                                data: data,
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                borderColor: 'rgba(25, 135, 84, 1)',
                                borderWidth: 1
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
                                            return 'Doanh thu: ' + context.raw.toLocaleString() +
                                            ' VNĐ';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString() + ' VNĐ';
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
// Biểu đồ doanh thu theo trạng thái
const revenueByStatusCtx = document.getElementById('revenueByStatusChart');
if (revenueByStatusCtx) {
    const statusData = @json($orderStatusStats);
    const labels = statusData.map(item => item.status_name);
    const data = statusData.map(item => item.total_value);
    const colors = statusData.map(item => item.color);

    new Chart(revenueByStatusCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu theo trạng thái',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.8', '1')),
                borderWidth: 1
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
                            return 'Doanh thu: ' + context.raw.toLocaleString() + ' VNĐ';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' VNĐ';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
// Biểu đồ trạng thái đơn hàng
const statusesCtx = document.getElementById('statusesChart');
if (statusesCtx) {
    const statusData = @json($orderStatusStats);
    const labels = statusData.map(item => item.status_name);
    const data = statusData.map(item => item.count);
    const colors = statusData.map(item => item.color);
    const revenues = statusData.map(item => item.total_value);

    new Chart(statusesCtx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            const revenue = revenues[context.dataIndex];
                            return [
                                `${label}: ${value} đơn (${percentage}%)`,
                                `Doanh thu: ${revenue.toLocaleString()} VNĐ`
                            ];
                        }
                    }
                }
            }
        }
    });
}

                // Biểu đồ khách hàng
                const customersCtx = document.getElementById('customersChart');
                if (customersCtx) {
                    const customerData = @json($customerLoyalty);
                    const labels = ['Khách hàng mới', 'Khách quay lại', 'Khách hàng thân thiết'];
                    const data = [customerData.new_customers, customerData.returning_customers, customerData
                        .loyal_customers
                    ];

                    new Chart(customersCtx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: [
                                    'rgba(13, 110, 253, 0.8)',
                                    'rgba(25, 135, 84, 0.8)',
                                    'rgba(255, 193, 7, 0.8)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw;
                                            const total = context.dataset.data.reduce((a, b) => a + b,
                                                0);
                                            const percentage = Math.round((value / total) * 100);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '70%'
                        }
                    });
                }

                // Biểu đồ tồn kho
                const inventoryCtx = document.getElementById('inventoryChart');
                if (inventoryCtx) {
                    const inventoryData = @json($inventoryStats);
                    const labels = ['Biến thể có hàng', 'Biến thể hết hàng'];
                    const data = [inventoryData.in_stock_variants, inventoryData.out_of_stock_variants];

                    new Chart(inventoryCtx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: [
                                    'rgba(25, 135, 84, 0.8)',
                                    'rgba(220, 53, 69, 0.8)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw;
                                            const total = context.dataset.data.reduce((a, b) => a + b,
                                                0);
                                            const percentage = Math.round((value / total) * 100);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.error('Canvas #revenueChart not found!');
                }
            @endif
        });
    </script>
@endsection

@push('styles')
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --info: #0dcaf0;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
            --purple: #6f42c1;
        }
        .info-box {
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            border-radius: 0.5rem;
            background-color: #fff;
            display: flex;
            margin-bottom: 1rem;
            min-height: 80px;
            padding: 0.5rem;
            position: relative;
        }
        .info-box .info-box-icon {
            border-radius: 0.5rem;
            align-items: center;
            display: flex;
            font-size: 1.875rem;
            justify-content: center;
            text-align: center;
            width: 70px;
        }
        .info-box .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.8;
            flex: 1;
            padding: 0 10px;
        }
        .info-box .info-box-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .info-box .progress {
            background-color: rgba(0, 0, 0, .125);
            height: 2px;
            margin: 5px 0;
        }
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }

        .fullscreen .chart-container {
            height: 80vh;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }

        .bg-purple {
            background-color: var(--purple) !important;
            color: white;
        }

        .table-purple {
            background-color: rgba(111, 66, 193, 0.1);
        }

        .table th {
            border-top: none;
        }

        .badge {
            font-size: 0.75em;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--secondary);
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background-color: transparent;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px);
        }
    </style>
@endpush
