
window.Pusher = Pusher; 

const EchoInstance = window.Echo.default;

window.Echo = new EchoInstance({
    broadcaster: 'pusher',
    key: '6b4e5bbb068bd2870452', 
    cluster: 'ap1',
    forceTLS: true
});

window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('[Pusher] Đã kết nối thành công! Stock ');
});


window.Echo.channel('product-stock')
    .listen('.stock.updated', function (data) {
        // console.log('[Stock Update] Nhận cập nhật tồn kho:', data);

        const badge = document.getElementById('stock-badge-' + data.variantId);
        if (!badge) return;

        const quantity = data.stock;
        badge.textContent = quantity > 0 ? quantity : 'Hết';

        
        badge.classList.remove('bg-success', 'bg-danger');
        badge.classList.add(quantity > 0 ? 'bg-success' : 'bg-danger');
    });


