window.Echo.channel('orders')
    .listen('.order.status', (data) => {
        console.log('📦 Dữ liệu nhận:', data);
        prependNewOrderRow(data); 
    });
