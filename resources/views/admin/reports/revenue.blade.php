@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Báo Cáo Doanh Thu</h5>
                @if (!$isEmpty)
                    {{-- <div>
                        <button class="btn btn-sm btn-light" id="exportBtn">
                            <i class="fas fa-download me-1"></i> Xuất Excel
                        </button>
                    </div> --}}
                @endif
            </div>

            <div class="card-body">
                <!-- Bộ lọc -->
                <form method="GET" action="{{ route('admin.reports.revenue') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Lọc nhanh</label>
                        <select name="filter" class="form-select" id="filterSelect">
                            <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="7days" {{ $filter === '7days' ? 'selected' : '' }}>7 ngày qua</option>
                            <option value="30days" {{ $filter === '30days' ? 'selected' : '' }}>30 ngày qua</option>
                            <option value="thismonth" {{ $filter === 'thismonth' ? 'selected' : '' }}>Tháng này</option>
                            <option value="custom" {{ $filter === 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="fromDateGroup" style="{{ $filter !== 'custom' ? 'display:none' : '' }}">
                        <label for="from_date" class="form-label">Từ ngày</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}"
                            id="fromDateInput">
                    </div>

                    <div class="col-md-3" id="toDateGroup" style="{{ $filter !== 'custom' ? 'display:none' : '' }}">
                        <label for="to_date" class="form-label">Đến ngày</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}"
                            id="toDateInput">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2" id="actionButtons"
                        style="{{ $filter !== 'custom' ? 'display:none' : '' }}">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me  -2"></i>Áp dụng
                        </button>
                        <a href="{{ route('admin.reports.revenue') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-sync me-2"></i>Reset
                        </a>
                    </div>
                </form>

                @if ($isEmpty)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Không có dữ liệu đơn hàng hoàn thành trong khoảng
                        thời gian từ {{ $fromDate }} đến {{ $toDate }}
                    </div>
                @else
                    <!-- Phần hiển thị báo cáo -->
                    <div class="row text-center mb-4">
                        <!-- Các card thống kê -->
                        <div class="col-md-3">
                            <div class="card bg-light shadow-sm">
                                <div class="card-body">
                                    <h6>Tổng doanh thu</h6>
                                    <h4 class="text-success">{{ number_format($summary->completed_revenue, 0, ',', '.') }}
                                        VNĐ</h4>
                                    <small class="text-muted">{{ $summary->completed_orders }} đơn hoàn thành</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light shadow-sm">
                                <div class="card-body">
                                    <h6>Tổng số đơn</h6>
                                    <h4>{{ $summary->total_orders }}</h4>
                                    <small class="text-muted">{{ $summary->paid_orders }} đơn đã thanh toán</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light shadow-sm">
                                <div class="card-body">
                                    <h6>Giá trị đơn TB</h6>
                                    <h4 class="text-info">{{ number_format($summary->avg_order_value, 0, ',', '.') }} VNĐ
                                    </h4>
                                    <small class="text-muted">Đơn hoàn thành</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light shadow-sm">
                                <div class="card-body">
                                    <h6>Đơn giá trị cao nhất</h6>
                                    <h4 class="text-warning">{{ number_format($summary->max_order_value, 0, ',', '.') }}
                                        VNĐ
                                    </h4>
                                    <small class="text-muted">Trong khoảng thời gian</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ doanh thu -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card shadow-sm h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Biểu đồ doanh thu theo ngày</span>
                                    <small class="text-muted">{{ $fromDate }} đến {{ $toDate }}</small>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">Thống kê trạng thái đơn hàng</div>
                                <div class="card-body">
                                    <canvas id="orderStatusChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top sản phẩm và danh mục -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header">Top 10 sản phẩm bán chạy</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Sản phẩm</th>
                                                    <th>SKU</th>
                                                    <th>Số lượng</th>
                                                    <th>Doanh thu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($topProducts as $index => $product)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if ($product->image)
                                                                    <img src="{{ asset('storage/' . $product->image) }}"
                                                                        alt="{{ $product->product_name }}"
                                                                        class="img-thumbnail me-2" width="40">
                                                                @endif
                                                                {{ $product->product_name }}
                                                            </div>
                                                        </td>
                                                        <td>{{ $product->sku }}</td>
                                                        <td>{{ $product->total_quantity }}</td>
                                                        <td>{{ number_format($product->total_revenue, 0, ',', '.') }} VNĐ
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header">Doanh thu theo danh mục</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Danh mục</th>
                                                    <th>Số lượng</th>
                                                    <th>Doanh thu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($revenueByCategory as $index => $category)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $category->name }}</td>
                                                        <td>{{ $category->total_quantity }}</td>
                                                        <td>{{ number_format($category->total_revenue, 0, ',', '.') }} VNĐ
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
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Xử lý bộ lọc
        document.getElementById('filterSelect').addEventListener('change', function() {
            const isCustom = this.value === 'custom';

            // Hiển thị/ẩn trường ngày
            document.getElementById('fromDateGroup').style.display = isCustom ? 'block' : 'none';
            document.getElementById('toDateGroup').style.display = isCustom ? 'block' : 'none';

            // Hiển thị/ẩn nút áp dụng và reset
            document.getElementById('actionButtons').style.display = isCustom ? 'flex' : 'none';

            // Tự động submit nếu không phải custom
            if (!isCustom) {
                this.form.submit();
            }
        });

        // Xử lý ngày
        document.getElementById('toDateInput')?.setAttribute('max', new Date().toISOString().split('T')[0]);
        document.getElementById('fromDateInput')?.addEventListener('change', function() {
            document.getElementById('toDateInput').setAttribute('min', this.value);
        });

        @if (!$isEmpty)
            // Khởi tạo biểu đồ doanh thu theo ngày kết hợp số đơn hàng
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($revenueByDate->pluck('date')) !!},
                    datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: {!! json_encode($revenueByDate->pluck('total_revenue')) !!},
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'left-y'
                        },
                        {
                            label: 'Số đơn hàng',
                            data: {!! json_encode($revenueByDate->pluck('order_count')) !!},
                            type: 'line',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            borderWidth: 2,
                            yAxisID: 'right-y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        'left-y': {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Doanh thu (VNĐ)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'decimal',
                                        // currency: 'VND',
                                        maximumFractionDigits: 0
                                    }).format(value);
                                }
                            }
                        },
                        'right-y': {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Số đơn hàng'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    return value; // hiển thị dạng số đơn
                                },
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return 'Ngày: ' + context[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        // doanh thu
                                        label += new Intl.NumberFormat('vi-VN', {
                                            style: 'currency',
                                            currency: 'VND'
                                        }).format(context.raw);
                                    } else {
                                        // số đơn hàng
                                        label += context.raw + ' đơn';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Biểu đồ thống kê trạng thái đơn hàng
            const ctxStatus = document.getElementById('orderStatusChart').getContext('2d');
            const statusChart = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($orderStatusStats->pluck('status')) !!},
                    datasets: [{
                        data: {!! json_encode($orderStatusStats->pluck('count')) !!},
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(199, 199, 199, 0.7)',
                            'rgba(255, 105, 180, 0.7)',
                            'rgba(0, 128, 0, 0.7)',
                            'rgba(255, 165, 0, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} đơn (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        @endif
    </script>
@endsection
