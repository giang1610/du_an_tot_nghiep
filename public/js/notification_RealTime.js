window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('[Pusher] ✅ Kết nối thành công!');
});

window.Echo.channel('admin-orders')
    .listen('.order.created', function (data) {
        console.log('[Realtime] Đơn hàng mới:', data);

        iziToast.success({
            message: `Bạn có đơn hàng mới: ${data.order_number}`,
            position: 'topRight',
            timeout: 5000
        });

        addOrderNotification(data.id, data.order_number);
    });

function addOrderNotification(orderId, orderNumber) {
    const notiList = document.getElementById('notiList');
    const notiCount = document.getElementById('notiCount');

    const newItem = document.createElement('li');
    newItem.innerHTML = `
        <a href="/admin/orders/${orderId}" class="dropdown-item noti-item">
            <i class="bi bi-receipt text-primary me-1"></i>
            Đơn hàng mới: <strong>${orderNumber}</strong>
        </a>
    `;

    const divider = notiList.querySelector('.dropdown-divider');
    if (divider && divider.parentNode === notiList) {
        notiList.insertBefore(newItem, divider);
    } else {
        notiList.appendChild(newItem);
    }

    // Cập nhật số badge
    const currentCount = parseInt(notiCount.innerText || '0');
    notiCount.innerText = currentCount + 1;
    notiCount.style.display = 'inline-block';
}
