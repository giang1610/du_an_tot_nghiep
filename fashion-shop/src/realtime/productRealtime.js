import echo from '../echo';

export function listenToProductChanged(onChanged) {
  const channel = echo.channel('products');

  echo.connector.pusher.connection.bind('connected', () => {
    // console.log('[Realtime] WebSocket kết nối thành công');
  });
  channel.listen('.product.changed', () => {
   
    if (typeof onChanged === 'function') {
      onChanged(); 
    }
  });

  channel.error((error) => {
    console.error('[Realtime] Lỗi channel products:', error);
  });

 
}
