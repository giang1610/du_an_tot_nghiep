@extends('admin.layouts.app')

@section('title', 'Báo cáo đơn hàng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Báo cáo đơn hàng</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary" id="exportExcel">
                            <i class="fas fa-file-excel"></i> Xuất Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Từ ngày</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Đến ngày</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select class="form-control" id="statusFilter">
                                    <option value="">Tất cả</option>
                                    <option value="completed">Hoàn thành</option>
                                    <option value="processing">Đang xử lý</option>
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="cancelled">Đã hủy</option>
                                    <option value="returned">Đã trả hàng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-success btn-block" id="filterOrders">
                                    <i class="fas fa-filter"></i> Lọc
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Orders will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div id="ordersPagination" class="float-right">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial orders
    loadOrders();
    
    // Filter orders
    $('#filterOrders').click(loadOrders);
    
    // Export to Excel
    $('#exportExcel').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const status = $('#statusFilter').val();
        
        window.location.href = '{{ route("admin.revenue.export-orders") }}?start_date=' + startDate + 
            '&end_date=' + endDate + '&status=' + status + '&type=excel';
    });
    
    // Function to load orders via AJAX
    function loadOrders(page = 1) {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const status = $('#statusFilter').val();
        
        $.get('{{ route("admin.orders.index") }}', {
            page: page,
            start_date: startDate,
            end_date: endDate,
            status: status
        }, function(data) {
            let html = '';
            
            if (data.data.length > 0) {
                data.data.forEach(order => {
                    html += `
                        <tr>
                            <td>${order.order_number}</td>
                            <td>${order.user ? order.user.name : 'Khách vãng lai'}</td>
                            <td>${new Date(order.created_at).toLocaleDateString()}</td>
                            <td>${order.total.toLocaleString()} đ</td>
                            <td><span class="badge ${getStatusBadgeClass(order.status)}">${getStatusText(order.status)}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info view-order-details" data-id="${order.id}">
                                    <i class="fas fa-eye"></i> Xem
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">Không có đơn hàng nào</td></tr>';
            }
            
            $('#ordersTable tbody').html(html);
            
            // Render pagination
            if (data.last_page > 1) {
                let paginationHtml = '<ul class="pagination">';
                
                // Previous page link
                if (data.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Trước</a></li>`;
                }
                
                // Page links
                for (let i = 1; i <= data.last_page; i++) {
                    paginationHtml += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                
                // Next page link
                if (data.current_page < data.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Sau</a></li>`;
                }
                
                paginationHtml += '</ul>';
                $('#ordersPagination').html(paginationHtml);
            } else {
                $('#ordersPagination').empty();
            }
        });
    }
    
    // Handle pagination clicks
    $(document).on('click', '#ordersPagination a', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadOrders(page);
    });
    
    // View order details
    $(document).on('click', '.view-order-details', function() {
        const orderId = $(this).data('id');
        
        $.get('{{ route("admin.orders.show", '') }}/${orderId}', function(data) {
            let itemsHtml = '';
            
            data.order_items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.product_variant.product.name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.price.toLocaleString()} đ</td>
                        <td>${(item.price * item.quantity).toLocaleString()} đ</td>
                    </tr>
                `;
            });
            
            const orderDetails = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>Thông tin đơn hàng</h5>
                        <p><strong>Mã đơn:</strong> ${data.order_number}</p>
                        <p><strong>Ngày đặt:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                        <p><strong>Trạng thái:</strong> <span class="badge ${getStatusBadgeClass(data.status)}">${getStatusText(data.status)}</span></p>
                        <p><strong>Tổng tiền:</strong> ${data.total.toLocaleString()} đ</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p><strong>Tên:</strong> ${data.user ? data.user.name : 'Khách vãng lai'}</p>
                        <p><strong>Email:</strong> ${data.user ? data.user.email : 'N/A'}</p>
                        <p><strong>Điện thoại:</strong> ${data.phone}</p>
                        <p><strong>Địa chỉ:</strong> ${data.shipping_address}</p>
                    </div>
                </div>
                
                <hr>
                
                <h5>Chi tiết sản phẩm</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
            `;
            
            $('#orderDetailsContent').html(orderDetails);
            $('#orderDetailsModal').modal('show');
        });
    });
    
    // Helper functions
    function getStatusText(status) {
        const statusTexts = {
            'pending': 'Chờ xử lý',
            'processing': 'Đang xử lý',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy',
            'shipped': 'Đã giao',
            'returned': 'Đã trả hàng'
        };
        
        return statusTexts[status] || status;
    }
    
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'pending': 'bg-secondary',
            'processing': 'bg-info',
            'completed': 'bg-success',
            'cancelled': 'bg-danger',
            'shipped': 'bg-primary',
            'returned': 'bg-warning'
        };
        
        return statusClasses[status] || 'bg-secondary';
    }
});
</script>
@endpush