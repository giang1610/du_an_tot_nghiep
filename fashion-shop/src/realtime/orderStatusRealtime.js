import Echo from '../echo';

export function listenToOrderStatusRealtime(callback) {
  console.log('[Realtime] Lắng nghe kênh: order-status');

  const channel = Echo.channel('order-status')
    .listen('.order.updated', (e) => {
      console.log('[Realtime] Nhận event:', e);
      if (e.orderId && e.newStatus) {
        callback(e.orderId, e.newStatus);
      }
    });

  return channel;
}
