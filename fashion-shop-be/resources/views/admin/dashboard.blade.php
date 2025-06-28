@extends('admin.layouts.app')
@section('content')
<main class="bg-light p-4">
    <div class="container mt-3">
        <h2 class="mb-4">Trang Quản Trị</h2>

        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary shadow">
                    <div class="card-body">
                        <h5 class="card-title">Sản phẩm đã bán</h5>
                        <p class="card-text fs-4">1,250</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success shadow">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng</h5>
                        <p class="card-text fs-4">328</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning shadow">
                    <div class="card-body">
                        <h5 class="card-title">Người dùng</h5>
                        <p class="card-text fs-4">152</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ phân tích tồn kho -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        Biểu đồ tồn kho theo danh mục
                    </div>
                    <div class="card-body">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Điện thoại', 'Laptop', 'Phụ kiện', 'Khác'],
                datasets: [{
                    label: 'Sản phẩm tồn kho',
                    data: [120, 90, 60, 30],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6c757d'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</main>

@endsection
