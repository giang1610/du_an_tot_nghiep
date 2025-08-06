window.Echo.channel('orders')
    .listen('.order.status', (data) => {
        console.log('Dữ liệu nhận:', data);
        prependNewOrderRow(data); 
    });



window.prependNewOrderRow = function (payload) {
    const order = payload.data;
    const product_name = payload.product_name;
    const color = payload.color;
    const size = payload.size;
    const quantity = payload.quantity;

    const newRow = document.createElement('tr');

    let paymentMethodBadge = '';
    let paymentStatusHTML = '';
    let orderStatusHTML = '';

    switch (order.payment_method) {
        case 'cod':
            paymentMethodBadge = `
                <span class="badge bg-info">
                    <i class="fas fa-money-bill-wave me-1"></i> COD
                </span>`;
            paymentStatusHTML = `
                <span class="">
                    <i class="fas fa-spinner fa-spin me-1"></i> Chờ thanh toán
                </span>`;
            orderStatusHTML = `
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-clock me-1"></i> Đang xử lý
                </span>`;
            break;

        case 'momo':
            paymentMethodBadge = `
                <span style="background-color: #A50064; color: white" class="badge">
                    <i class="fas fa-mobile-alt me-1"></i> Momo
                </span>`;
            paymentStatusHTML = `
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> Đã thanh toán
                </span>`;
            orderStatusHTML = `
                <span class="badge bg-primary">
                    <i class="fas fa-check-circle me-1"></i> Đang xử lý
                </span>`;
            break;

        case 'vnpay':
            paymentMethodBadge = `
                <span class="badge bg-success">
                    <i class="fas fa-credit-card me-1"></i> VNPAY
                </span>`;
            paymentStatusHTML = `
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> Đã thanh toán
                </span>`;
            orderStatusHTML = `
                <span class="badge bg-primary">
                    <i class="fas fa-check-circle me-1"></i> Đang xử lý
                </span>`;
            break;

        default:
            paymentMethodBadge = `
                <span class="badge bg-light text-dark">
                    <i class="fas fa-question me-1"></i> Khác
                </span>`;
            paymentStatusHTML = `
                <span class="badge bg-secondary">
                    <i class="fas fa-question-circle me-1"></i> Không rõ
                </span>`;
            orderStatusHTML = `
                <span class="badge bg-secondary">
                    <i class="fas fa-question-circle me-1"></i> Không rõ
                </span>`;
            break;
    }

    newRow.innerHTML = `
        <td>
            <strong>${order.order_number ?? 'ORD-' + order.id}</strong>
            <div class="text-muted small">
                ${new Date(order.created_at).toLocaleString('vi-VN')}
            </div>
        </td>
        <td>
            ${order.user?.name ?? 'Khách vãng lai'}
            <div class="text-muted small">
                ${order.user?.phone ?? ''}
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center mb-2">
                <div>
                    ${product_name}
                    <div class="text-muted small">
                        ${color ?? ''} | ${size ?? ''} x${quantity ?? 0}
                    </div>
                </div>
            </div>
        </td>
        <td>
            <div class="small">
                <div><i class="fas fa-truck me-2"></i> ${order.shipping_method ?? ''}</div>
                <div><i class="fas fa-map-marker-alt me-2"></i> ${order.shipping_address ?? ''}</div>
            </div>
        </td>
        <td>
            <strong>${Number(order.total).toLocaleString()} VNĐ</strong>
        </td>
        <td>
            ${paymentMethodBadge}
            <div class="small mt-1">
                ${paymentStatusHTML}
            </div>
        </td>
        <td>
            ${orderStatusHTML}
        </td>
        <td>
            <div class="d-flex flex-column gap-2">
                <a href="/admin/orders/${order.id}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                    <span class="d-none d-md-inline">Xem chi tiết</span>
                    <i class="fas fa-eye"></i>
                </a>
                <a href="/admin/orders/${order.id}/edit" class="btn btn-sm btn-outline-success" title="Cập nhật">
                    <span class="d-none d-md-inline">Cập nhật</span>
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        </td>
    `;

    const tbody = document.querySelector("tbody");
    if (tbody) {
        tbody.prepend(newRow);
    }
};
