document.addEventListener('DOMContentLoaded', function () {
    const notiList = document.getElementById('notiList');
    const notiCount = document.getElementById('notiCount');
    const bell = document.getElementById('notificationBell');
    let readOrderIds = new Set(JSON.parse(localStorage.getItem('readOrderIds') || '[]'));

    updateUnreadDisplay();

    notiList.addEventListener('click', function (e) {
        const anchor = e.target.closest('.noti-item');
        if (anchor) {
            const href = anchor.getAttribute('href');
            const match = href.match(/\/orders\/(\d+)/);
            if (match) {
                const id = match[1];
                readOrderIds.add(id);
                localStorage.setItem('readOrderIds', JSON.stringify([...readOrderIds]));
                anchor.classList.remove('unread');
                updateUnreadDisplay();
            }
        }
    });

    window.Echo.connector.pusher.connection.bind('connected', function () {
        console.log('[Pusher] ✅ Kết nối thành công!');
    });

    window.Echo.channel('admin-orders')
        .listen('.order.created', function (data) {
            console.log('[Realtime] Đơn hàng mới:', data);

            const orderIdStr = data.id.toString();
            if (readOrderIds.has(orderIdStr)) return;

            // Rung chuông nhẹ
            bell.classList.add('bell-animate');
            setTimeout(() => bell.classList.remove('bell-animate'), 1000);

            // Toast đẹp hơn
            iziToast.show({
                title: '🛒 Đơn hàng mới!',
                message: `<strong>${data.order_number}</strong> vừa được tạo.`,
                position: 'topRight',
                timeout: 3000,
                backgroundColor: '#4f46e5',
                theme: 'dark',
                progressBarColor: 'white',
                titleColor: 'white',
                messageColor: 'white',
                onOpening: function (instance, toast) {
                    toast.style.top = '50px';
                    toast.style.right = '30px';
                }
            });

            addOrderNotification(data.id, data.order_number);
        });

    function addOrderNotification(orderId, orderNumber) {
        const newItem = document.createElement('li');
        newItem.innerHTML = `
            <a href="/admin/orders/${orderId}" class="dropdown-item noti-item unread d-flex align-items-start gap-2 px-3 py-2">
                <i class="bi bi-receipt text-primary fs-5"></i>
                <div class="flex-grow-1">
                    <div>Đơn hàng: <strong>${orderNumber}</strong></div>
                    <small class="text-muted">Đơn hàng mới</small>
                </div>
                <span class="dot-unread"></span>
            </a>
        `;

        const header = notiList.querySelector('.dropdown-header');
        if (header && header.parentNode === notiList) {
            notiList.insertBefore(newItem, header.nextElementSibling.nextElementSibling);
        } else {
            notiList.insertBefore(newItem, notiList.firstChild);
        }

        updateUnreadDisplay();
    }

    function updateUnreadDisplay() {
        let unreadCount = 0;
        document.querySelectorAll('#notiList .noti-item').forEach(item => {
            const href = item.getAttribute('href');
            const match = href.match(/\/orders\/(\d+)/);
            if (match) {
                const id = match[1];
                if (!readOrderIds.has(id)) {
                    item.classList.add('unread');
                    unreadCount++;
                } else {
                    item.classList.remove('unread');
                }
            }
        });

        if (unreadCount > 0) {
            notiCount.innerText = unreadCount;
            notiCount.style.display = 'inline-block';
        } else {
            notiCount.innerText = '0';
            notiCount.style.display = 'none';
        }
    }
});
