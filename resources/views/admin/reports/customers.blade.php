@extends('admin.layouts.app')

@section('title', 'Báo cáo khách hàng')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <select name="filter" class="form-control">
                            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="7days" {{ request('filter') == '7days' ? 'selected' : '' }}>7 ngày</option>
                            <option value="30days" {{ request('filter') == '30days' ? 'selected' : '' }}>30 ngày</option>
                            <option value="thismonth" {{ request('filter') == 'thismonth' ? 'selected' : '' }}>Tháng này</option>
                            <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>1 năm</option>
                            <option value="thisyear" {{ request('filter') == 'thisyear' ? 'selected' : '' }}>Năm nay</option>
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                        </select>
                    </div>

                    <div class="form-group mr-3" id="date-range" style="{{ request('filter') != 'custom' ? 'display:none' : '' }}">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', Carbon::now()->subDays(30)->toDateString()) }}">
                        <span class="mx-2">đến</span>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', Carbon::now()->toDateString()) }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.reports.export.customers', request()->query()) }}" class="btn btn-success ml-2">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                </form>
            </div>
        </div>

        <!-- Customer Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-body bg-primary text-white">
                    <h5 class="card-title">Khách hàng mới</h5>
                    <h3 class="card-text">{{ $newCustomers }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body bg-info text-white">
                    <h5 class="card-title">Khách hàng quay lại</h5>
                    <h3 class="card-text">{{ $returningCustomers }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body bg-warning text-white">
                    <h5 class="card-title">Tỉ lệ hủy đơn</h5>
                    <h3 class="card-text">{{ $orderCancellationRate }}%</h3>
                </div>
            </div>
        </div>

        <!-- Customer Demographics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Phân tích nhân khẩu học</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Phân bố theo khu vực</h6>
                        <canvas id="locationChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Phân bố theo độ tuổi</h6>
                        <canvas id="ageChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Behavior -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Hành vi mua hàng</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tần suất mua hàng</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Số đơn</th>
                                        <th>Ngày giữa các đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseBehavior['frequency'] as $customer)
                                    <tr>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->order_count }}</td>
                                        <td>{{ round($customer->days_between_orders, 1) }} ngày</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Giỏ hàng trung bình</h6>
                        <div class="card card-body">
                            <div class="mb-3">
                                <strong>Giá trị đơn trung bình:</strong> 
                                {{ number_format($purchaseBehavior['basketSize']->avg_order_value) }}đ
                            </div>
                            <div>
                                <strong>Số sản phẩm trung bình:</strong> 
                                {{ round($purchaseBehavior['basketSize']->avg_items_per_order, 1) }}
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

    // Location Chart
    const locationCtx = document.getElementById('locationChart').getContext('2d');
    const locationChart = new Chart(locationCtx, {
        type: 'bar',
        data: {
            labels: @json($customerDemographics['byLocation']->pluck('location')),
            datasets: [{
                label: 'Số khách hàng',
                data: @json($customerDemographics['byLocation']->pluck('count')),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Age Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: @json($customerDemographics['byAgeGroup']->pluck('age_group')),
            datasets: [{
                data: @json($customerDemographics['byAgeGroup']->pluck('count')),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
});
</script>
@endpush