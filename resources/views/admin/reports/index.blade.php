@extends('admin.layouts.app')

@section('title', 'Phân tích doanh thu')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tổng quan doanh thu</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chọn khoảng thời gian</label>
                                <select class="form-control" id="revenuePeriod">
                                    <option value="day">Ngày</option>
                                    <option value="week">Tuần</option>
                                    <option value="month" selected>Tháng</option>
                                    <option value="year">Năm</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>So sánh với</label>
                                <select class="form-control" id="compareWith">
                                    <option value="previous">Kỳ trước</option>
                                    <option value="same">Cùng kỳ năm ngoái</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Doanh thu hiện tại</span>
                                    <span class="info-box-number" id="currentRevenue">0 đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-chart-bar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Doanh thu so sánh</span>
                                    <span class="info-box-number" id="compareRevenue">0 đ</span>
                                    <span class="info-box-text" id="percentageChange">
                                        <span class="text-success"><i class="fas fa-caret-up"></i> 0%</span> so với kỳ trước
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart">
                        <canvas id="revenueComparisonChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top sản phẩm bán chạy</h3>
                    <div class="card-tools">
                        <select class="form-control form-control-sm" id="productPeriod">
                            <option value="day">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                            <option value="all">Tất cả</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Doanh số</th>
                                    <th>Số lượng</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsTable">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tình trạng đơn hàng</h3>
                    <div class="card-tools">
                        <select class="form-control form-control-sm" id="orderStatusPeriod">
                            <option value="day">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="orderStatusChart" height="200"></canvas>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-group list-group-unbordered" id="orderStatusList">
                                <!-- Dynamic content will be loaded here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Khách hàng tiềm năng</h3>
                    <div class="card-tools">
                        <select class="form-control form-control-sm" id="customerType">
                            <option value="potential">Tiềm năng</option>
                            <option value="frequent">Thường xuyên</option>
                            <option value="returned">Hoàn hàng</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Số đơn</th>
                                    <th>Tổng chi</th>
                                </tr>
                            </thead>
                            <tbody id="customerTable">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tồn kho</h3>
                    <div class="card-tools">
                        <button class="btn btn-tool" data-toggle="collapse" data-target="#inventoryFilter">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="inventoryFilter" class="collapse p-3 bg-light">
                        <div class="form-group">
                            <label>Ngưỡng cảnh báo</label>
                            <input type="number" class="form-control" id="inventoryThreshold" value="10" min="1">
                        </div>
                    </div>
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link active" href="#lowStock" data-toggle="tab">Sắp hết</a></li>
                            <li class="nav-item"><a class="nav-link" href="#outOfStock" data-toggle="tab">Hết hàng</a></li>
                            <li class="nav-item"><a class="nav-link" href="#longInventory" data-toggle="tab">Tồn kho lâu</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="lowStock">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th>Số lượng</th>
                                                <th>Cập nhật</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lowStockTable">
                                            <!-- Dynamic content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="outOfStock">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th>Số lượng</th>
                                                <th>Cập nhật</th>
                                            </tr>
                                        </thead>
                                        <tbody id="outOfStockTable">
                                            <!-- Dynamic content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="longInventory">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th>Tồn kho</th>
                                                <th>Đã bán (3 tháng)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="longInventoryTable">
                                            <!-- Dynamic content will be loaded here -->
                                        </tbody>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize charts
    const revenueChart = new Chart($('#revenueComparisonChart'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Doanh thu',
                    backgroundColor: ['#007bff', '#6c757d'],
                    data: []
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' đ';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toLocaleString() + ' đ';
                        }
                    }
                }
            }
        }
    });
    
    const orderStatusChart = new Chart($('#orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#28a745', // Completed
                    '#ffc107', // Processing
                    '#dc3545', // Cancelled
                    '#6c757d', // Pending
                    '#17a2b8', // Shipped
                    '#6f42c1'  // Returned
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Load initial data
    loadRevenueComparison();
    loadTopProducts();
    loadOrderStatusStats();
    loadCustomerAnalysis();
    loadInventoryStats();
    
    // Event listeners for filters
    $('#revenuePeriod, #compareWith').change(loadRevenueComparison);
    $('#productPeriod').change(loadTopProducts);
    $('#orderStatusPeriod').change(loadOrderStatusStats);
    $('#customerType').change(loadCustomerAnalysis);
    $('#inventoryThreshold').change(loadInventoryStats);
    
    // Functions to load data via AJAX
    function loadRevenueComparison() {
        const period = $('#revenuePeriod').val();
        const compareWith = $('#compareWith').val();
        
        $.get('{{ route("admin.revenue.compare") }}', {
            period: period,
            compare_with: compareWith
        }, function(data) {
            $('#currentRevenue').text(data.current.toLocaleString() + ' đ');
            $('#compareRevenue').text(data.compare.toLocaleString() + ' đ');
            
            const percentageElem = $('#percentageChange span');
            percentageElem.removeClass('text-success text-danger');
            
            if (data.is_increase) {
                percentageElem.addClass('text-success').html('<i class="fas fa-caret-up"></i> ' + data.percentage_change + '%');
            } else {
                percentageElem.addClass('text-danger').html('<i class="fas fa-caret-down"></i> ' + Math.abs(data.percentage_change) + '%');
            }
            
            // Update chart
            revenueChart.data.labels = data.labels;
            revenueChart.data.datasets[0].data = [data.current, data.compare];
            revenueChart.update();
        });
    }
    
    function loadTopProducts() {
        const period = $('#productPeriod').val();
        
        $.get('{{ route("admin.revenue.by-product") }}', {
            period: period,
            type: 'product',
            limit: 10
        }, function(products) {
            let html = '';
            
            products.forEach(product => {
                html += `
                    <tr>
                        <td>${product.name}</td>
                        <td>${product.total_orders}</td>
                        <td>${product.total_revenue.toLocaleString()} đ</td>
                    </tr>
                `;
            });
            
            $('#topProductsTable').html(html || '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>');
        });
    }
    
    function loadOrderStatusStats() {
        const period = $('#orderStatusPeriod').val();
        
        $.get('{{ route("admin.revenue.order-status") }}', {
            period: period
        }, function(data) {
            // Update chart
            const statusLabels = [];
            const statusData = [];
            const backgroundColors = [];
            
            // Define status colors and order
            const statusOrder = ['completed', 'processing', 'pending', 'cancelled', 'shipped', 'returned'];
            const statusColors = {
                'completed': '#28a745',
                'processing': '#ffc107',
                'pending': '#6c757d',
                'cancelled': '#dc3545',
                'shipped': '#17a2b8',
                'returned': '#6f42c1'
            };
            
            statusOrder.forEach(status => {
                if (data.statuses[status]) {
                    statusLabels.push(status.charAt(0).toUpperCase() + status.slice(1));
                    statusData.push(data.statuses[status].count);
                    backgroundColors.push(statusColors[status]);
                }
            });
            
            orderStatusChart.data.labels = statusLabels;
            orderStatusChart.data.datasets[0].data = statusData;
            orderStatusChart.data.datasets[0].backgroundColor = backgroundColors;
            orderStatusChart.update();
            
            // Update status list
            let statusHtml = '';
            let total = data.total_orders;
            
            for (const [status, stats] of Object.entries(data.statuses)) {
                statusHtml += `
                    <li class="list-group-item">
                        <b>${status.charAt(0).toUpperCase() + status.slice(1)}</b>
                        <span class="float-right">
                            ${stats.count} <span class="text-muted">(${stats.percentage}%)</span>
                        </span>
                    </li>
                `;
            }
            
            $('#orderStatusList').html(statusHtml);
        });
    }
    
    function loadCustomerAnalysis() {
        const type = $('#customerType').val();
        const period = type === 'returned' ? 'month' : 'year';
        
        $.get('{{ route("admin.revenue.customer-analysis") }}', {
            type: type,
            period: period,
            limit: 10
        }, function(customers) {
            let html = '';
            
            customers.forEach(customer => {
                const totalSpent = customer.total_spent ? customer.total_spent.toLocaleString() + ' đ' : 'N/A';
                const totalOrders = customer.total_orders || customer.returned_orders || 0;
                
                html += `
                    <tr>
                        <td>${customer.name} <small class="text-muted">${customer.email}</small></td>
                        <td>${totalOrders}</td>
                        <td>${totalSpent}</td>
                    </tr>
                `;
            });
            
            $('#customerTable').html(html || '<tr><td colspan="3" class="text-center">Không có dữ liệu</td></tr>');
        });
    }
    
    function loadInventoryStats() {
        const threshold = $('#inventoryThreshold').val();
        
        $.get('{{ route("admin.revenue.inventory-stats") }}', {
            threshold: threshold
        }, function(data) {
            // Low stock
            let lowStockHtml = '';
            data.low_stock.forEach(item => {
                lowStockHtml += `
                    <tr>
                        <td>${item.product_variant.product.name}</td>
                        <td><span class="badge bg-warning">${item.quantity}</span></td>
                        <td>${new Date(item.updated_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
            $('#lowStockTable').html(lowStockHtml || '<tr><td colspan="3" class="text-center">Không có sản phẩm nào sắp hết hàng</td></tr>');
            
            // Out of stock
            let outOfStockHtml = '';
            data.out_of_stock.forEach(item => {
                outOfStockHtml += `
                    <tr>
                        <td>${item.product_variant.product.name}</td>
                        <td><span class="badge bg-danger">${item.quantity}</span></td>
                        <td>${new Date(item.updated_at).toLocaleDateString()}</td>
                    </tr>
                `;
            });
            $('#outOfStockTable').html(outOfStockHtml || '<tr><td colspan="3" class="text-center">Không có sản phẩm nào hết hàng</td></tr>');
            
            // Long inventory
            let longInventoryHtml = '';
            data.long_inventory.forEach(product => {
                const inventory = product.variants.reduce((sum, variant) => sum + (variant.inventory?.quantity || 0), 0);
                
                longInventoryHtml += `
                    <tr>
                        <td>${product.name}</td>
                        <td>${inventory}</td>
                        <td>${product.sold_last_3_months || 0}</td>
                    </tr>
                `;
            });
            $('#longInventoryTable').html(longInventoryHtml || '<tr><td colspan="3" class="text-center">Không có sản phẩm tồn kho lâu</td></tr>');
        });
    }
});
</script>
@endpush