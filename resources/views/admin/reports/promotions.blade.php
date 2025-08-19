@extends('admin.layouts.app')

@section('title', 'Báo cáo khuyến mãi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hiệu quả chiến dịch khuyến mãi</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Chọn chiến dịch</label>
                                <select class="form-control" id="promotionSelect">
                                    <option value="">Tất cả chiến dịch</option>
                                    @foreach($promotions as $promotion)
                                        <option value="{{ $promotion->id }}">{{ $promotion->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Khoảng thời gian</label>
                                <select class="form-control" id="promotionPeriod">
                                    <option value="all">Tất cả</option>
                                    <option value="month">Tháng này</option>
                                    <option value="year">Năm nay</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-success btn-block" id="filterPromotions">
                                    <i class="fas fa-filter"></i> Lọc
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart">
                        <canvas id="promotionChart" height="150"></canvas>
                    </div>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered" id="promotionTable">
                            <thead>
                                <tr>
                                    <th>Chiến dịch</th>
                                    <th>Tổng đơn</th>
                                    <th>Đơn hoàn thành</th>
                                    <th>Tỷ lệ hoàn thành</th>
                                    <th>Doanh thu</th>
                                    <th>Hiệu quả</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Promotion data will be loaded here -->
                            </tbody>
                        </table>
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
    // Initialize chart
    const promotionChart = new Chart($('#promotionChart'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Tổng đơn',
                    backgroundColor: '#007bff',
                    data: []
                },
                {
                    label: 'Đơn hoàn thành',
                    backgroundColor: '#28a745',
                    data: []
                },
                {
                    label: 'Doanh thu',
                    backgroundColor: '#ffc107',
                    data: [],
                    type: 'line',
                    yAxisID: 'y-axis-1'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                },
                'y-axis-1': {
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
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
                            let label = context.dataset.label || '';
                            if (label === 'Doanh thu') {
                                return label + ': ' + context.raw.toLocaleString() + ' đ';
                            }
                            return label + ': ' + context.raw;
                        }
                    }
                }
            }
        }
    });
    
    // Load initial promotion data
    loadPromotionStats();
    
    // Filter promotions
    $('#filterPromotions').click(loadPromotionStats);
    
    // Function to load promotion stats
    function loadPromotionStats() {
        const promotionId = $('#promotionSelect').val();
        const period = $('#promotionPeriod').val();
        
        $.get('{{ route("admin.revenue.promotion-stats") }}', {
            promotion_id: promotionId,
            period: period
        }, function(data) {
            // Update chart
            const labels = [];
            const totalOrders = [];
            const completedOrders = [];
            const revenues = [];
            
            data.forEach(promotion => {
                labels.push(promotion.name);
                totalOrders.push(promotion.total_orders);
                completedOrders.push(promotion.completed_orders);
                revenues.push(promotion.revenue);
            });
            
            promotionChart.data.labels = labels;
            promotionChart.data.datasets[0].data = totalOrders;
            promotionChart.data.datasets[1].data = completedOrders;
            promotionChart.data.datasets[2].data = revenues;
            promotionChart.update();
            
            // Update table
            let html = '';
            
            if (data.length > 0) {
                data.forEach(promotion => {
                    const completionRate = promotion.total_orders > 0 
                        ? Math.round((promotion.completed_orders / promotion.total_orders) * 100) 
                        : 0;
                    
                    const efficiency = promotion.completed_orders > 0 
                        ? Math.round(promotion.revenue / promotion.completed_orders)
                        : 0;
                    
                    html += `
                        <tr>
                            <td>${promotion.name}</td>
                            <td>${promotion.total_orders}</td>
                            <td>${promotion.completed_orders}</td>
                            <td>
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-success" style="width: ${completionRate}%"></div>
                                </div>
                                <span class="badge bg-success">${completionRate}%</span>
                            </td>
                            <td>${promotion.revenue.toLocaleString()} đ</td>
                            <td>${efficiency.toLocaleString()} đ/đơn</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
            }
            
            $('#promotionTable tbody').html(html);
        });
    }
});
</script>
@endpush