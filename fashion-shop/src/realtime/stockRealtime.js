import echo from '../echo';

export function listenToStockUpdates(callback) {
  const channel = echo.channel('product-stock');
  console.log('[Realtime] Lắng nghe: product-stock');

  channel.listen('.stock.updated', callback);

 
}
