@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Báo Cáo Khách Hàng</h5>
        </div>

        <div class="card-body">
            <!-- Bộ lọc -->
            <form method="GET" action="{{ route('admin.reports.customers') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="from_date" class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Lọc dữ liệu</button>
                </div>
            </form>

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">Top 10 khách hàng mua nhiều nhất</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Khách hàng</th>
                                            <th>Số đơn</th>
                                            <th>Tổng chi tiêu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topCustomers as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>{{ $customer->name }}</div>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            </td>
                                            <td>{{ $customer->total_orders }}</td>
                                            <td>{{ number_format($customer->total_spent, 0, ',', '.') }} đ</td>
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
                        <div class="card-header">Khách hàng mới</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Khách hàng</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Điện thoại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($newCustomers as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>{{ $customer->name }}</div>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            </td>
                                            <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $customer->phone }}</td>
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
</div>
@endsection