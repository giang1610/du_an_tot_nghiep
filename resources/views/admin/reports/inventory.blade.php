@extends('admin.layouts.app')

@section('title', 'Báo cáo tồn kho')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Cảnh báo tồn thấp:</label>
                        <input type="number" name="threshold" class="form-control" value="{{ request('threshold', 10) }}" min="1" style="width: 80px">
                    </div>

                    <div class="form-group mr-3">
                        <label class="mr-2">Hàng tồn lâu (tháng):</label>
                        <input type="number" name="months_old" class="form-control" value="{{ request('months_old', 3) }}" min="1" max="24" style="width: 80px">
                    </div>

                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.reports.export.inventory', request()->query()) }}" class="btn btn-success ml-2">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                </form>
            </div>
        </div>

        <!-- Inventory Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-body bg-primary text-white mb-4">
                    <h5 class="card-title">Tổng sản phẩm</h5>
                    <h3 class="card-text">{{ $inventoryStats['totalProducts'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body bg-info text-white mb-4">
                    <h5 class="card-title">Tổng biến thể</h5>
                    <h3 class="card-text">{{ $inventoryStats['totalVariants'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body bg-success text-white mb-4">
                    <h5 class="card-title">Tổng tồn kho</h5>
                    <h3 class="card-text">{{ $inventoryStats['totalStockQuantity'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body bg-warning text-white mb-4">
                    <h5 class="card-title">Hết hàng</h5>
                    <h3 class="card-text">{{ $inventoryStats['outOfStock'] }}</h3>
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Sản phẩm sắp hết hàng (dưới {{ request('threshold', 10) }} sản phẩm)</h5>
            </div>
            <div class="card-body">
                @if($lowStockProducts->isEmpty())
                <div class="alert alert-info">Không có sản phẩm nào sắp hết hàng</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th>Màu sắc</th>
                                <th>Kích thước</th>
                                <th>Số lượng</th>
                                <th>Giá bán</th>
                                <th>Giá vốn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ?? '/images/default-product.jpg' }}" width="40" height="40" class="rounded mr-2">
                                        {{ $product->product->name }}
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->color->name }}</td>
                                <td>{{ $product->size->name }}</td>
                                <td class="{{ $product->quantity == 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ $product->quantity }}
                                </td>
                                <td>{{ number_format($product->price) }}đ</td>
                                <td>{{ number_format($product->cost_price) }}đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Old Stock Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Sản phẩm tồn kho lâu (trên {{ request('months_old', 3) }} tháng)</h5>
            </div>
            <div class="card-body">
                @if($oldStockProducts->isEmpty())
                <div class="alert alert-info">Không có sản phẩm nào tồn kho lâu</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th>Số lượng</th>
                                <th>Giá vốn</th>
                                <th>Giá trị tồn</th>
                                <th>Cập nhật cuối</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($oldStockProducts as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ?? '/images/default-product.jpg' }}" width="40" height="40" class="rounded mr-2">
                                        {{ $product->product->name }}
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>{{ number_format($product->cost_price) }}đ</td>
                                <td>{{ number_format($product->quantity * $product->cost_price) }}đ</td>
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <!-- Inventory Movement -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Hoạt động tồn kho gần đây</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th>Số lượng</th>
                                <th>Giá vốn</th>
                                <th>Giá trị tồn</th>
                                <th>Cập nhật cuối</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryMovement as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product->image ?? '/images/default-product.jpg' }}" width="40" height="40" class="rounded mr-2">
                                        {{ $product->product->name }}
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>{{ number_format($product->cost_price) }}đ</td>
                                <td>{{ number_format($product->quantity * $product->cost_price) }}đ</td>
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection