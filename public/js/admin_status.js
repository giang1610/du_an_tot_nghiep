window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('status Kết nối thành công!');
});

window.Echo.channel('admin-orders')
    .listen('.order.fail', (data) => {
        const order = data;
        console.log('data', data);
        console.log(` Nhận đơn hàng bị hủy: #${order.id} `);

            const statusTd = document.getElementById(`order-status-${order.id}`);

            if (statusTd) {
                statusTd.innerHTML = `
                    <span class="badge bg-danger">
                        <i class="fas fa-times-circle me-1"></i> Đã hủy
                    </span>
                `;
            } 
        }
    );
