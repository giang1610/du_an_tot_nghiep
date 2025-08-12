// Khi kết nối thành công với Pusher
window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('hello ưinsdasdasd');
});

// Lắng nghe sự kiện thay đổi trạng thái đơn hàng
window.Echo.channel('admin_status')
    .listen('.order.status', (data) => {
        const order = data;
        console.log('data', data);
        console.log(`Nhận đơn hàng bị cập nhật: #${order.id}`);

        const statusTd = document.getElementById(`order-status-${order.id}`);
        if (!statusTd) return;

        switch (order.status) {
            case 'cancelled':
                statusTd.innerHTML = `
                    <span class="badge bg-danger">
                        <i class="fas fa-times-circle me-1"></i> Đã hủy
                    </span>
                `;
                // Xoá nút thao tác cũ (nếu có)
                const oldBtn = document.getElementById(`button-remove-${order.id}`);
                if (oldBtn) oldBtn.remove();
                console.log('xóa button ok');
                break;

            case 'return_requested':
                statusTd.innerHTML = `
                    <span class="badge bg-info">
                        <i class="fas fa-exchange-alt me-1"></i> Yêu cầu hoàn hàng
                    </span>
                `;
                appendReturnRequestButton(order);
                break;
        }
    });

/**
 * Thêm nút "Yêu cầu hoàn hàng" vào cột thao tác
 */
function appendReturnRequestButton(order) {
    // Nếu nút đã tồn tại thì không thêm lại
    if (document.getElementById(`order-actions-${order.id}`)) return;

    const returnBtn = document.createElement('a');
    returnBtn.id = `order-actions-${order.id}`;
    returnBtn.href = `/admin/orders/${order.id}/edit`;
    returnBtn.className = 'btn btn-sm btn-outline-success me-2'; 
    returnBtn.setAttribute('data-bs-toggle', 'tooltip');
    returnBtn.setAttribute('title', 'Yêu cầu hoàn hàng');

    returnBtn.innerHTML = `
        <i class="fas fa-edit me-1"></i>
    `;
    returnBtn.style.margin = '1px';          

    // Tìm ô thao tác trong hàng đơn hàng
    const actionsTd = document.querySelector(`#order-status-${order.id}`)
        ?.closest('tr')
        ?.querySelector('td:last-child');

    if (actionsTd) {
        actionsTd.appendChild(returnBtn);
        new bootstrap.Tooltip(returnBtn);
    }
}