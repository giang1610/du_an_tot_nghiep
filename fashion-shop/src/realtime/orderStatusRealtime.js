import Echo from '../echo';

export function listenToOrderStatusRealtime(callback) {
  console.log('[Realtime] Lắng nghe kênh: order-status');

  const channel = Echo.channel('order-status')
    .listen('.order.updated', (e) => {
      console.log('[Realtime] Nhận event:', e);

      const orderId = e.orderId ?? e.order_id; // hỗ trợ cả camelCase & snake_case
      const newStatus = e.newStatus ?? e.status;
      const paymentStatus = e.paymentStatus ?? e.payment_status;

      if (orderId) {
        callback(orderId, newStatus, paymentStatus);
      }
    });

  return channel;
}
