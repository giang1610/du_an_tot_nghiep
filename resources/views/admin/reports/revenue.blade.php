@extends('admin.layouts.app')

@section('title', 'Báo cáo doanh thu chi tiết')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Báo cáo doanh thu chi tiết</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reports.revenue') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt mr-1"></i> Khoảng thời gian</label>
                        <select name="time_period" class="form-control select2" style="width: 100%;">
                            @foreach($periodOptions as $value => $label)
                                <option value="{{ $value }}" {{ $time_period == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2" id="from_date_group" style="{{ $time_period != 'custom' ? 'display:none' : '' }}">
                    <div class="form-group">
                        <label><i class="far fa-calendar mr-1"></i> Từ ngày</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>
                </div>
                
                <div class="col-md-2" id="to_date_group" style="{{ $time_period != 'custom' ? 'display:none' : '' }}">
                    <div class="form-group">
                        <label><i class="far fa-calendar mr-1"></i> Đến ngày</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-chart-line mr-1"></i> So sánh với</label>
                        <select name="compare_with" class="form-control select2" style="width: 100%;">
                            <option value="">-- Không so sánh --</option>
                            @foreach($compareOptions as $value => $label)
                                <option value="{{ $value }}" {{ $compareWith == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        @if(!$isEmpty)
                            <button type="submit" name="export" value="1" class="btn btn-success">
                                <i class="fas fa-file-excel mr-1"></i> Xuất Excel
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
        
        @if($isEmpty)
            <div class="alert alert-info">
                <i class="icon fas fa-info-circle"></i> Không có dữ liệu trong khoảng thời gian từ {{ $fromDate }} đến {{ $toDate }}
            </div>
        @else
            <!-- Thông tin khoảng thời gian -->
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> Thông tin báo cáo</h5>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Khoảng thời gian:</strong> {{ $fromDate }} đến {{ $toDate }}
                    </div>
                    <div class="col-md-4">
                        <strong>Tổng số ngày:</strong> {{ Carbon\Carbon::parse($fromDate)->diffInDays(Carbon\Carbon::parse($toDate)) + 1 }} ngày
                    </div>
                    <div class="col-md-4">
                        <strong>Ngày tạo báo cáo:</strong> {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
            
            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Thống kê tổng quan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-info elevation-1">
                                            <i class="fas fa-shopping-cart"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng đơn hàng</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_orders) }}
                                                @if($compareData)
                                                    <small class="{{ $summary->total_orders >= $compareData['summary']->total_orders ? 'text-success' : 'text-danger' }}">
                                                        @php
                                                            $diff = $summary->total_orders - $compareData['summary']->total_orders;
                                                            $percent = $compareData['summary']->total_orders ? ($diff/$compareData['summary']->total_orders)*100 : 100;
                                                        @endphp
                                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }} ({{ round($percent) }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-success elevation-1">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng doanh thu</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_revenue) }}đ
                                                @if($compareData)
                                                    <small class="{{ $summary->total_revenue >= $compareData['summary']->total_revenue ? 'text-success' : 'text-danger' }}">
                                                        @php
                                                            $diff = $summary->total_revenue - $compareData['summary']->total_revenue;
                                                            $percent = $compareData['summary']->total_revenue ? ($diff/$compareData['summary']->total_revenue)*100 : 100;
                                                        @endphp
                                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }}đ ({{ round($percent) }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-warning elevation-1">
                                            <i class="fas fa-percentage"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Giá trị đơn TB</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->avg_order_value) }}đ
                                                @if($compareData)
                                                    <small class="{{ $summary->avg_order_value >= $compareData['summary']->avg_order_value ? 'text-success' : 'text-danger' }}">
                                                        @php
                                                            $diff = $summary->avg_order_value - $compareData['summary']->avg_order_value;
                                                            $percent = $compareData['summary']->avg_order_value ? ($diff/$compareData['summary']->avg_order_value)*100 : 100;
                                                        @endphp
                                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }}đ ({{ round($percent) }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm">
                                        <span class="info-box-icon bg-danger elevation-1">
                                            <i class="fas fa-tags"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng giảm giá</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary->total_discount) }}đ
                                                @if($compareData)
                                                    <small class="{{ $summary->total_discount >= $compareData['summary']->total_discount ? 'text-success' : 'text-danger' }}">
                                                        @php
                                                            $diff = $summary->total_discount - $compareData['summary']->total_discount;
                                                            $percent = $compareData['summary']->total_discount ? ($diff/$compareData['summary']->total_discount)*100 : 100;
                                                        @endphp
                                                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }}đ ({{ round($percent) }}%)
                                                    </small>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm bg-gradient-light">
                                        <span class="info-box-icon bg-primary elevation-1">
                                            <i class="fas fa-receipt"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Doanh thu trước thuế</span>
                                            <span class="info-box-number">{{ number_format($summary->subtotal) }}đ</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm bg-gradient-light">
                                        <span class="info-box-icon bg-secondary elevation-1">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng thuế</span>
                                            <span class="info-box-number">{{ number_format($summary->total_tax) }}đ</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm bg-gradient-light">
                                        <span class="info-box-icon bg-info elevation-1">
                                            <i class="fas fa-truck"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Phí vận chuyển</span>
                                            <span class="info-box-number">{{ number_format($summary->total_shipping) }}đ</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box shadow-sm bg-gradient-light">
                                        <span class="info-box-icon bg-purple elevation-1">
                                            <i class="fas fa-chart-line"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Đơn hàng/ngày</span>
                                            <span class="info-box-number">
                                                @php
                                                    $days = Carbon\Carbon::parse($fromDate)->diffInDays(Carbon\Carbon::parse($toDate)) + 1;
                                                    $avg = $days > 0 ? $summary->total_orders / $days : 0;
                                                @endphp
                                                {{ number_format($avg, 1) }}
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
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Biểu đồ doanh thu theo ngày
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="revenueChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Các tab thông tin chi tiết -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="payment-methods-tab" data-toggle="pill" href="#payment-methods" role="tab" aria-controls="payment-methods" aria-selected="true">
                                        <i class="fas fa-credit-card mr-1"></i> Thanh toán
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="categories-tab" data-toggle="pill" href="#categories" role="tab" aria-controls="categories" aria-selected="false">
                                        <i class="fas fa-tags mr-1"></i> Danh mục
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="products-tab" data-toggle="pill" href="#products" role="tab" aria-controls="products" aria-selected="false">
                                        <i class="fas fa-boxes mr-1"></i> Sản phẩm
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="statuses-tab" data-toggle="pill" href="#statuses" role="tab" aria-controls="statuses" aria-selected="false">
                                        <i class="fas fa-clipboard-list mr-1"></i> Trạng thái
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="customers-tab" data-toggle="pill" href="#customers" role="tab" aria-controls="customers" aria-selected="false">
                                        <i class="fas fa-users mr-1"></i> Khách hàng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="vouchers-tab" data-toggle="pill" href="#vouchers" role="tab" aria-controls="vouchers" aria-selected="false">
                                        <i class="fas fa-ticket-alt mr-1"></i> Voucher
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="inventory-tab" data-toggle="pill" href="#inventory" role="tab" aria-controls="inventory" aria-selected="false">
                                        <i class="fas fa-warehouse mr-1"></i> Tồn kho
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-three-tabContent">
                                <!-- Phương thức thanh toán -->
                                <div class="tab-pane fade show active" id="payment-methods" role="tabpanel" aria-labelledby="payment-methods-tab">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover table-striped">
                                                    <thead class="bg-primary">
                                                        <tr>
                                                            <th class="text-center">Phương thức</th>
                                                            <th class="text-center">Số đơn</th>
                                                            <th class="text-center">Doanh thu</th>
                                                            <th class="text-center">Tỷ lệ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $totalRevenue = $revenueByPaymentMethod->sum('total_revenue'); @endphp
                                                        @foreach($revenueByPaymentMethod as $method)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span class="badge bg-primary">{{ $method->payment_method }}</span>
                                                                </td>
                                                                <td class="text-right">{{ number_format($method->order_count) }}</td>
                                                                <td class="text-right">{{ number_format($method->total_revenue) }}đ</td>
                                                                <td>
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-success" style="width: {{ $totalRevenue > 0 ? ($method->total_revenue/$totalRevenue)*100 : 0 }}%"></div>
                                                                    </div>
                                                                    <small class="text-muted">{{ $totalRevenue > 0 ? round(($method->total_revenue/$totalRevenue)*100, 1) : 0 }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="bg-light">
                                                            <th class="text-center">Tổng cộng</th>
                                                            <th class="text-right">{{ number_format($revenueByPaymentMethod->sum('order_count')) }}</th>
                                                            <th class="text-right">{{ number_format($totalRevenue) }}đ</th>
                                                            <th>100%</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="chart">
                                                <canvas id="paymentMethodsChart" height="250"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Danh mục sản phẩm -->
                                <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover table-striped">
                                                    <thead class="bg-success">
                                                        <tr>
                                                            <th>Danh mục</th>
                                                            <th class="text-center">SL bán</th>
                                                            <th class="text-center">Doanh thu</th>
                                                            <th class="text-center">Tỷ lệ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $totalRevenue = $revenueByCategory->sum('total_revenue'); @endphp
                                                        @foreach($revenueByCategory as $category)
                                                            <tr>
                                                                <td>
                                                                    <a href="#" class="text-dark">{{ $category->name }}</a>
                                                                </td>
                                                                <td class="text-right">{{ number_format($category->total_quantity) }}</td>
                                                                <td class="text-right">{{ number_format($category->total_revenue) }}đ</td>
                                                                <td>
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-success" style="width: {{ $totalRevenue > 0 ? ($category->total_revenue/$totalRevenue)*100 : 0 }}%"></div>
                                                                    </div>
                                                                    <small class="text-muted">{{ $totalRevenue > 0 ? round(($category->total_revenue/$totalRevenue)*100, 1) : 0 }}%</small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="bg-light">
                                                            <th>Tổng cộng</th>
                                                            <th class="text-right">{{ number_format($revenueByCategory->sum('total_quantity')) }}</th>
                                                            <th class="text-right">{{ number_format($totalRevenue) }}đ</th>
                                                            <th>100%</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="chart">
                                                <canvas id="categoriesChart" height="250"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sản phẩm bán chạy -->
                                <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped">
                                            <thead class="bg-warning">
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
                                                @php $totalRevenue = $topProducts->sum('total_revenue'); @endphp
                                                @foreach($topProducts as $index => $product)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <a href="#" class="text-dark">{{ $product->product_name }}</a>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">{{ $product->sku }}</span>
                                                        </td>
                                                        <td class="text-right">{{ number_format($product->total_quantity) }}</td>
                                                        <td class="text-right">{{ number_format($product->total_revenue) }}đ</td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-warning" style="width: {{ $totalRevenue > 0 ? ($product->total_revenue/$totalRevenue)*100 : 0 }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $totalRevenue > 0 ? round(($product->total_revenue/$totalRevenue)*100, 1) : 0 }}%</small>
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
                                        <div class="col-md-5">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover table-striped">
                                                    <thead class="bg-danger">
                                                        <tr>
                                                            <th class="text-center">Trạng thái</th>
                                                            <th class="text-center">Số đơn</th>
                                                            <th class="text-center">Tỷ lệ</th>
                                                            <th class="text-center">Tổng giá trị</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $totalOrders = $orderStatusStats->sum('count'); @endphp
                                                        @foreach($orderStatusStats as $status)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span class="badge bg-{{ $status->status == 'completed' ? 'success' : ($status->status == 'cancelled' ? 'danger' : 'primary') }}">
                                                                        {{ $status->status_name }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-right">{{ number_format($status->count) }}</td>
                                                                <td>
                                                                    <div class="progress progress-xs">
                                                                        <div class="progress-bar bg-primary" style="width: {{ $totalOrders > 0 ? ($status->count/$totalOrders)*100 : 0 }}%"></div>
                                                                    </div>
                                                                    <small class="text-muted">{{ $totalOrders > 0 ? round(($status->count/$totalOrders)*100, 1) : 0 }}%</small>
                                                                </td>
                                                                <td class="text-right">{{ number_format($status->total_value) }}đ</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="bg-light">
                                                            <th class="text-center">Tổng cộng</th>
                                                            <th class="text-right">{{ number_format($totalOrders) }}</th>
                                                            <th>100%</th>
                                                            <th class="text-right">{{ number_format($orderStatusStats->sum('total_value')) }}đ</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="chart">
                                                <canvas id="statusesChart" height="250"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Thông tin khách hàng -->
                                <div class="tab-pane fade" id="customers" role="tabpanel" aria-labelledby="customers-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-info elevation-1">
                                                    <i class="fas fa-user-plus"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách hàng mới</span>
                                                    <span class="info-box-number">
                                                        {{ $customerLoyalty->new_customers }}
                                                        <small class="text-muted">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->new_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-success elevation-1">
                                                    <i class="fas fa-user-check"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách quay lại</span>
                                                    <span class="info-box-number">
                                                        {{ $customerLoyalty->returning_customers }}
                                                        <small class="text-muted">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->returning_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-warning elevation-1">
                                                    <i class="fas fa-user-tag"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Khách hàng thân thiết</span>
                                                    <span class="info-box-number">
                                                        {{ $customerLoyalty->loyal_customers }}
                                                        <small class="text-muted">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->loyal_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Phân bố khách hàng</h3>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="customersChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Thống kê khách hàng</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 50%">Tổng số khách hàng mua hàng:</th>
                                                                <td class="text-right">{{ number_format($customerLoyalty->total_customers) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ khách hàng mới:</th>
                                                                <td class="text-right">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->new_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ khách quay lại:</th>
                                                                <td class="text-right">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->returning_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ khách thân thiết:</th>
                                                                <td class="text-right">{{ $customerLoyalty->total_customers > 0 ? round(($customerLoyalty->loyal_customers/$customerLoyalty->total_customers)*100, 1) : 0 }}%</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Voucher sử dụng -->
                                <div class="tab-pane fade" id="vouchers" role="tabpanel" aria-labelledby="vouchers-tab">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped">
                                            <thead class="bg-purple">
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
                                                @php $totalUsage = $voucherUsage->sum('usage_count'); @endphp
                                                @foreach($voucherUsage as $voucher)
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $voucher->code }}</span>
                                                        </td>
                                                        <td>{{ $voucher->name }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $voucher->type == 'shipping' ? 'info' : 'success' }}">
                                                                {{ $voucher->type == 'shipping' ? 'Miễn phí vận chuyển' : 'Giảm giá sản phẩm' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-right">{{ $voucher->usage_count }}</td>
                                                        <td class="text-right">{{ number_format($voucher->total_discount) }}đ</td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-purple" style="width: {{ $totalUsage > 0 ? ($voucher->usage_count/$totalUsage)*100 : 0 }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $totalUsage > 0 ? round(($voucher->usage_count/$totalUsage)*100, 1) : 0 }}%</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if($voucherUsage->count() > 0)
                                                    <tr class="bg-light">
                                                        <th colspan="3" class="text-center">Tổng cộng</th>
                                                        <th class="text-right">{{ $totalUsage }}</th>
                                                        <th class="text-right">{{ number_format($voucherUsage->sum('total_discount')) }}đ</th>
                                                        <th>100%</th>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Không có dữ liệu voucher được sử dụng</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Thông tin tồn kho -->
                                <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-info elevation-1">
                                                    <i class="fas fa-boxes"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tổng sản phẩm</span>
                                                    <span class="info-box-number">{{ $inventoryStats->total_products }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-success elevation-1">
                                                    <i class="fas fa-box-open"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Sản phẩm có hàng</span>
                                                    <span class="info-box-number">
                                                        {{ $inventoryStats->in_stock_products }}
                                                        <small class="text-muted">{{ $inventoryStats->total_products > 0 ? round(($inventoryStats->in_stock_products/$inventoryStats->total_products)*100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-danger elevation-1">
                                                    <i class="fas fa-times-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Sản phẩm hết hàng</span>
                                                    <span class="info-box-number">
                                                        {{ $inventoryStats->out_of_stock_products }}
                                                        <small class="text-muted">{{ $inventoryStats->total_products > 0 ? round(($inventoryStats->out_of_stock_products/$inventoryStats->total_products)*100, 1) : 0 }}%</small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box shadow">
                                                <span class="info-box-icon bg-warning elevation-1">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Giá trị tồn kho</span>
                                                    <span class="info-box-number">{{ number_format($inventoryStats->total_inventory_value) }}đ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Tình trạng tồn kho</h3>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="inventoryChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Thống kê tồn kho</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 50%">Tổng số biến thể:</th>
                                                                <td class="text-right">{{ number_format($inventoryStats->total_variants) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ sản phẩm có hàng:</th>
                                                                <td class="text-right">{{ $inventoryStats->total_products > 0 ? round(($inventoryStats->in_stock_products/$inventoryStats->total_products)*100, 1) : 0 }}%</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tỷ lệ sản phẩm hết hàng:</th>
                                                                <td class="text-right">{{ $inventoryStats->total_products > 0 ? round(($inventoryStats->out_of_stock_products/$inventoryStats->total_products)*100, 1) : 0 }}%</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Giá trị tồn kho trung bình:</th>
                                                                <td class="text-right">{{ $inventoryStats->total_products > 0 ? number_format($inventoryStats->total_inventory_value/$inventoryStats->total_products) : 0 }}đ/sản phẩm</td>
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
    <div class="card-footer text-muted">
        <small>Báo cáo được tạo lúc {{ now()->format('H:i:s d/m/Y') }}</small>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/animate.css/animate.min.css') }}">
<style>
    .info-box {
        min-height: 80px;
        margin-bottom: 0;
        transition: all 0.3s ease-in-out;
    }
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .info-box-icon {
        height: 80px;
        line-height: 80px;
        width: 80px;
        font-size: 35px;
        transition: all 0.3s ease-in-out;
    }
    .info-box-content {
        padding: 10px;
    }
    .info-box-number {
        font-size: 1.4rem;
        font-weight: bold;
    }
    .progress-xs {
        height: 7px;
    }
    .nav-tabs .nav-link {
        border-top-width: 3px;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link.active {
        border-top-color: #007bff;
    }
    .table th {
        white-space: nowrap;
    }
    .chart {
        position: relative;
        min-height: 200px;
    }
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
    }
    .tab-content {
        padding: 15px;
        background-color: #fff;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0 0 0.25rem 0.25rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .bg-gradient-light {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(!$isEmpty)
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra canvas và dữ liệu
        console.log('Initializing charts...');
        
        // Biểu đồ doanh thu
        var revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            console.log('Revenue Data:', {!! json_encode($revenueByDate) !!});
            
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueByDate->pluck('date')) !!},
                    datasets: [{
                        label: 'Doanh thu',
                        data: {!! json_encode($revenueByDate->pluck('total_revenue')) !!},
                        backgroundColor: 'rgba(60, 141, 188, 0.2)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + 'đ';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error('Canvas #revenueChart not found!');
        }
    });
@endif


$(document).ready(function() {
    // Khởi tạo select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Hiển thị/ẩn ngày tùy chọn
    $('select[name="time_period"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#from_date_group, #to_date_group').slideDown();
        } else {
            $('#from_date_group, #to_date_group').slideUp();
        }
    });
    
    @if(!$isEmpty)
        // Đảm bảo Chart.js đã được load
        if (typeof Chart !== 'undefined') {
            // Biểu đồ doanh thu theo ngày
            var revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                var revenueChart = new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($revenueByDate->pluck('date')) !!},
                        datasets: [{
                            label: 'Doanh thu',
                            data: {!! json_encode($revenueByDate->pluck('total_revenue')) !!},
                            backgroundColor: 'rgba(60, 141, 188, 0.2)',
                            borderColor: 'rgba(60, 141, 188, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: 'rgba(60, 141, 188, 1)',
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: 'rgba(60, 141, 188, 1)',
                            pointHoverBorderColor: '#fff',
                            pointHitRadius: 10,
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#343a40',
                                titleFont: {
                                    size: 14
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    label: function(context) {
                                        return 'Doanh thu: ' + context.raw.toLocaleString() + 'đ';
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
                                        return value.toLocaleString() + 'đ';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
            
            // Biểu đồ phương thức thanh toán
            var paymentMethodsCtx = document.getElementById('paymentMethodsChart');
            if (paymentMethodsCtx) {
                var paymentMethodsChart = new Chart(paymentMethodsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($revenueByPaymentMethod->pluck('payment_method')) !!},
                        datasets: [{
                            data: {!! json_encode($revenueByPaymentMethod->pluck('total_revenue')) !!},
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
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
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#343a40',
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value.toLocaleString() + 'đ (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '70%',
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });
            }
            
            // Biểu đồ doanh thu theo danh mục
            var categoriesCtx = document.getElementById('categoriesChart');
            if (categoriesCtx) {
                var categoriesChart = new Chart(categoriesCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($revenueByCategory->pluck('name')) !!},
                        datasets: [{
                            label: 'Doanh thu theo danh mục',
                            data: {!! json_encode($revenueByCategory->pluck('total_revenue')) !!},
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
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
                                backgroundColor: '#343a40',
                                callbacks: {
                                    label: function(context) {
                                        return 'Doanh thu: ' + context.raw.toLocaleString() + 'đ';
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
                                        return value.toLocaleString() + 'đ';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 2000
                        }
                    }
                });
            }
            
            // Biểu đồ trạng thái đơn hàng
            var statusesCtx = document.getElementById('statusesChart');
            if (statusesCtx) {
                var statusesChart = new Chart(statusesCtx, {
                    type: 'pie',
                    data: {
                        labels: {!! json_encode($orderStatusStats->pluck('status_name')) !!},
                        datasets: [{
                            data: {!! json_encode($orderStatusStats->pluck('count')) !!},
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.7)',
                                'rgba(220, 53, 69, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(0, 123, 255, 0.7)',
                                'rgba(108, 117, 125, 0.7)',
                                'rgba(23, 162, 184, 0.7)'
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
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#343a40',
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value + ' đơn (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });
            }
            
            // Biểu đồ khách hàng
            var customersCtx = document.getElementById('customersChart');
            if (customersCtx) {
                var customersChart = new Chart(customersCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Khách hàng mới', 'Khách quay lại', 'Khách hàng thân thiết'],
                        datasets: [{
                            data: [
                                {{ $customerLoyalty->new_customers }},
                                {{ $customerLoyalty->returning_customers }},
                                {{ $customerLoyalty->loyal_customers }}
                            ],
                            backgroundColor: [
                                'rgba(23, 162, 184, 0.7)',
                                'rgba(40, 167, 69, 0.7)',
                                'rgba(255, 193, 7, 0.7)'
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
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#343a40',
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '70%',
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });
            }
            
            // Biểu đồ tồn kho
            var inventoryCtx = document.getElementById('inventoryChart');
            if (inventoryCtx) {
                var inventoryChart = new Chart(inventoryCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Có hàng', 'Hết hàng'],
                        datasets: [{
                            data: [
                                {{ $inventoryStats->in_stock_products }},
                                {{ $inventoryStats->out_of_stock_products }}
                            ],
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.7)',
                                'rgba(220, 53, 69, 0.7)'
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
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#343a40',
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });
            }
        }
    @endif

    // Thêm hiệu ứng cho các phần tử
    $('.card').hover(
        function() {
            $(this).addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).removeClass('animate__animated animate__pulse');
        }
    );

    // Sparkline cho các số liệu nhỏ
    $('.sparkline').each(function() {
        var $this = $(this);
        $this.sparkline('html', {
            type: 'line',
            width: '100%',
            height: '50',
            lineColor: $this.data('color'),
            fillColor: 'rgba(0, 123, 255, 0.1)',
            spotColor: undefined,
            minSpotColor: undefined,
            maxSpotColor: undefined,
            highlightSpotColor: undefined,
            highlightLineColor: undefined,
            spotRadius: 2,
            lineWidth: 1
        });
    });
});
</script> 
@endpush