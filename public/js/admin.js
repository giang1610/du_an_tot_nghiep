window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('new Kết nối thành công!');
});

window.Echo.channel('orders')
    .listen('.order.status', (data) => {
        // Nhận được đơn hàng mới -> thêm vào đầu danh sách
        prependNewOrderRow(data.data);
    });
