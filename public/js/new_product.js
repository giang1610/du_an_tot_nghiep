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
                        <i class="fas fa-clock me-1"></i> Chờ xử lý
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
                    <span class="badge bg-warning">
                        <i class="fas fa-check-circle me-1"></i> Chờ xử lý
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
                    <span class="badge bg-warning">
                        <i class="fas fa-check-circle me-1"></i> Chờ xử lý
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
            <td id="order-status-${order.id}">
                ${orderStatusHTML}
            </td>
            <td>
                <div class="d-flex gap-2 align-items-center">
                    <a href="/admin/orders/${order.id}" 
                        class="btn btn-sm btn-outline-primary" 
                        data-bs-toggle="tooltip" 
                        title="Xem chi tiết" 
                        aria-label="Xem chi tiết đơn hàng">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="javascript:void(0);"
                        class="btn btn-sm btn-outline-warning show-status-select"
                        data-order-id="${order.id}"
                        id="button-remove-${order.id}"
                        data-current-status="${order.status}"
                        title="Đổi trạng thái đơn hàng">
                        <i class="fas fa-exchange-alt"></i>
                    </a>
                </div>
            </td>
        `;

        const tbody = document.querySelector("tbody");
        if (tbody) {
            tbody.prepend(newRow);
        }

        const changeStatusBtn = newRow.querySelector('.show-status-select');
        if (changeStatusBtn) {
            changeStatusBtn.addEventListener('click', function () {
                const orderId = this.dataset.orderId;
                showSingleStatusSelect(this, orderId);
            });
        }
    };

    function showSingleStatusSelect(button, orderId) {
    // Xóa tất cả form đang mở khác
    document.querySelectorAll('.status-select-form').forEach(el => el.remove());

    const form = document.createElement('form');
    form.className = 'status-select-form position-absolute bg-white p-2 rounded shadow border';
    form.method = 'POST';
    form.action = `/admin/orders/${orderId}`;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrf}">
        <input type="hidden" name="_method" value="PUT">
        <select name="status" class="form-select form-select-sm">
            <option disabled selected>Chuyển trạng thái...</option>
            <option value="processing">Đang xử lý</option>
        </select>
    `;

    form.querySelector('select').addEventListener('change', function () {
        iziToast.success({ message: 'Đang cập nhật trạng thái...', position: 'topRight' });
        form.submit();
    });

    // GẮN FORM NGAY DƯỚI BUTTON
    button.parentElement.style.position = 'relative';
    form.style.position = 'absolute';
    form.style.top = '36px'; // dưới nút
    form.style.right = '0'; // Căn sát phải của button
    form.style.minWidth = '200px';
    form.style.zIndex = '1000';

    button.parentElement.appendChild(form);
}

